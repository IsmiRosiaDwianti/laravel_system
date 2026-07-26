<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceLog;
use Illuminate\Http\Request;
use App\Services\ServiceMonitorService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ServiceApiController extends Controller
{
    /**
     * ============================================================
     *  📡 API: GET ALL SERVICES
     *  ============================================================
     *  🔗 URL: GET /api/services
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=10&page=1
     * ============================================================
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            
            $services = Service::orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            foreach ($services as $service) {
                $service->uptime = $this->calculateUptime($service->id, 30);
            }
            
            return response()->json([
                'success' => true,
                'data' => $services->items(),
                'pagination' => [
                    'total' => $services->total(),
                    'per_page' => $services->perPage(),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE DETAIL (SIMPLE)
     *  ============================================================
     *  🔗 URL: GET /api/services/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function show($id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->uptime = $this->calculateUptime($service->id, 30);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'target' => $service->target,
                    'type' => $service->type,
                    'last_status' => $service->last_status ?? 'UNKNOWN',
                    'last_code' => $service->last_code ?? '-',
                    'last_response_time' => $service->last_response_time ?? 0,
                    'last_message' => $service->last_message ?? '-',
                    'wa_interval_minutes' => $service->wa_interval_minutes ?? 5,
                    'uptime' => $service->uptime,
                    'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service tidak ditemukan'
            ], 404);
        }
    }

    /**
     * ============================================================
     *  📡 API: CREATE SERVICE
     *  ============================================================
     *  🔗 URL: POST /api/services
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "name": "...", "target": "...", "type": "http", "wa_interval": 5 }
     * ============================================================
     */
    public function store(Request $request, ServiceMonitorService $monitor)
    {
        try {
            // 🔥 VALIDASI DINAMIS BERDASARKAN TIPE           
            $rules = [
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(['http', 'https', 'ping', 'port'])],
                'wa_interval' => 'nullable|integer|min:0|max:1440',
            ];

            // 🔥 Validasi target berdasarkan tipe
            if (in_array($request->type, ['http', 'https', 'port'])) {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^https?:\/\/.+/', $value)) {
                            $fail('Format URL tidak valid. Harus diawali dengan http:// atau https://');
                        }
                    },
                ];
            } else if ($request->type === 'ping') {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                ];
            }

            $validated = $request->validate($rules);

            // 🔥 PERBAIKI: Fix target berdasarkan tipe
            $validated['target'] = $this->fixTarget($validated['target'], $validated['type']);

            if (Service::where('target', $validated['target'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target "' . $validated['target'] . '" sudah digunakan'
                ], 422);
            }

            if (Service::where('name', $validated['name'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama service "' . $validated['name'] . '" sudah digunakan'
                ], 422);
            }

            // 🔥 BUAT SERVICE dengan default 5 menit
            $service = Service::create([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'last_status' => 'UNKNOWN',
                'wa_interval_minutes' => $validated['wa_interval'] ?? 5,
            ]);

            $monitor->check($service);

            return response()->json([
                'success' => true,
                'message' => 'Service berhasil ditambahkan',
                'data' => $service
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: UPDATE SERVICE
     *  ============================================================
     *  🔗 URL: PUT /api/services/{id}
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "name": "...", "target": "...", "type": "http", "wa_interval": 5 }
     * ============================================================
     */
    public function update(Request $request, $id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);

            // 🔥 VALIDASI DINAMIS BERDASARKAN TIPE
            $rules = [
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(['http', 'https', 'ping', 'port'])],
                'wa_interval' => 'nullable|integer|min:0|max:1440',
            ];

            if (in_array($request->type, ['http', 'https', 'port'])) {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^https?:\/\/.+/', $value)) {
                            $fail('Format URL tidak valid. Harus diawali dengan http:// atau https://');
                        }
                    },
                ];
            } else if ($request->type === 'ping') {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                ];
            }

            $validated = $request->validate($rules);

            // 🔥 PERBAIKI: Fix target berdasarkan tipe
            $validated['target'] = $this->fixTarget($validated['target'], $validated['type']);

            if (Service::where('target', $validated['target'])->where('id', '!=', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target "' . $validated['target'] . '" sudah digunakan'
                ], 422);
            }

            if (Service::where('name', $validated['name'])->where('id', '!=', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama service "' . $validated['name'] . '" sudah digunakan'
                ], 422);
            }

            // 🔥 UPDATE SERVICE
            $service->update([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'wa_interval_minutes' => $validated['wa_interval'] ?? $service->wa_interval_minutes,
            ]);

            $monitor->check($service);

            return response()->json([
                'success' => true,
                'message' => 'Service berhasil diupdate',
                'data' => $service
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: DELETE SERVICE
     *  ============================================================
     *  🔗 URL: DELETE /api/services/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service "' . $serviceName . '" berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: CHECK SERVICE (MANUAL)
     *  ============================================================
     *  🔗 URL: POST /api/services/{id}/check
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: (kosong)
     * ============================================================
     */
    public function check($id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);
            $monitor->check($service);
            
            $service->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Service "' . $service->name . '" berhasil di-check',
                'data' => [
                    'status' => $service->last_status ?? 'UNKNOWN',
                    'response_code' => $service->last_code ?? 'N/A',
                    'response_time' => $service->last_response_time ?? 0,
                    'message' => $service->last_message ?? '-',
                    'checked_at' => $service->last_check_at 
                        ? $service->last_check_at->format('Y-m-d H:i:s') 
                        : '-'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal check service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE LOGS
     *  ============================================================
     *  🔗 URL: GET /api/services/{id}/logs
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=20&status=UP&date_from=2026-01-01&date_to=2026-01-31
     * ============================================================
     */
    public function logs($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            
            $perPage = $request->input('per_page', 20);
            $status = $request->input('status');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $query = ServiceLog::where('service_id', $id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $logs = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs->items(),
                'pagination' => [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data logs: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE DETAIL (LENGKAP)
     *  ============================================================
     *  🔗 URL: GET /api/services/{id}/detail
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function detail($id)
    {
        try {
            $service = Service::findOrFail($id);
            $stats = $this->getServiceStats($service->id);
            $uptime = $this->calculateUptime($service->id, 30);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'target' => $service->target,
                    'type' => $service->type,
                    'last_status' => $service->last_status ?? 'UNKNOWN',
                    'last_code' => $service->last_code ?? '-',
                    'last_response_time' => $service->last_response_time ?? 0,
                    'last_message' => $service->last_message ?? '-',
                    'last_action' => $service->last_action ?? '-',
                    'wa_interval_minutes' => $service->wa_interval_minutes ?? 5,
                    'last_check_at' => $service->last_check_at 
                        ? $service->last_check_at->format('Y-m-d H:i:s') 
                        : null,
                    'uptime_30d' => $uptime,
                    'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                    'stats' => $stats,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service tidak ditemukan'
            ], 404);
        }
    }

    /**
     * ============================================================
     *  📡 API: DOWNLOAD REPORT PDF
     *  ============================================================
     *  🔗 URL: GET /api/services/{id}/download-report
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?date_from=2026-01-01&date_to=2026-01-31
     * ============================================================
     */
    public function downloadReport($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            
            $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            $logs = ServiceLog::where('service_id', $id)
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->orderBy('created_at', 'asc')
                ->get();

            $reportData = $this->generateReportData($service, $logs, $dateFrom, $dateTo);

            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                return response()->json([
                    'success' => false,
                    'message' => 'DomPDF tidak terinstall. Jalankan: composer require barryvdh/laravel-dompdf'
                ], 500);
            }

            $filename = 'laporan_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $service->name) 
                . '_' . $dateFrom . '_to_' . $dateTo . '.pdf';
            
            if (!view()->exists('reports.service-pdf')) {
                return response()->json([
                    'success' => false,
                    'message' => 'View reports.service-pdf tidak ditemukan. Buat file di resources/views/reports/service-pdf.blade.php'
                ], 500);
            }

            $pdf = Pdf::loadView('reports.service-pdf', compact('reportData'));
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔥 API STATUS UNTUK AJAX POLLING
     *  ============================================================
     *  🔗 URL: GET /api/services/status
     *  🔑 Butuh Auth: Sanctum Token (optional)
     * ============================================================
     */
    public function status()
    {
        try {
            $services = Service::all();
            
            return response()->json([
                'success' => true,
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'target' => $service->target,
                        'last_status' => $service->last_status ?? 'UNKNOWN',
                        'last_code' => $service->last_code ?? '-',
                        'wa_interval_minutes' => $service->wa_interval_minutes ?? 5,
                        'last_check_at' => $service->last_check_at 
                            ? \Carbon\Carbon::parse($service->last_check_at)
                                ->setTimezone('Asia/Jakarta')
                                ->format('H:i:s') 
                            : '-',
                    ];
                })
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
              ->header('Pragma', 'no-cache')
              ->header('Expires', '0');
              
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE OVERVIEW
     *  ============================================================
     *  🔗 URL: GET /api/services/overview
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function overview()
    {
        try {
            $totalServices = Service::count();
            $totalUp = Service::where('last_status', 'UP')->count();
            $totalWarning = Service::where('last_status', 'WARNING')->count();
            $totalDown = Service::where('last_status', 'DOWN')->count();
            $totalUnknown = Service::where('last_status', 'UNKNOWN')->count();

            $servicesByType = Service::select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();

            $recentIssues = ServiceLog::where('status', 'DOWN')
                ->orWhere('status', 'WARNING')
                ->with('service')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($log) {
                    return [
                        'service' => $log->service->name ?? 'Unknown',
                        'status' => $log->status,
                        'message' => $log->message,
                        'time' => $log->created_at->diffForHumans()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalServices,
                    'up' => $totalUp,
                    'warning' => $totalWarning,
                    'down' => $totalDown,
                    'unknown' => $totalUnknown,
                    'uptime_percentage' => $totalServices > 0 
                        ? round(($totalUp / $totalServices) * 100, 2) 
                        : 0,
                    'services_by_type' => $servicesByType,
                    'recent_issues' => $recentIssues
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil overview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE HEALTH
     *  ============================================================
     *  🔗 URL: GET /api/services/{id}/health
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function health($id)
    {
        try {
            $service = Service::findOrFail($id);
            
            $lastLog = $service->logs()->latest()->first();
            
            $healthScore = 100;
            if ($service->last_status == 'DOWN') {
                $healthScore = 0;
            } elseif ($service->last_status == 'WARNING') {
                $healthScore = 50;
            } elseif ($service->last_status == 'UP') {
                if ($lastLog && $lastLog->response_time > 3) {
                    $healthScore = 70;
                } elseif ($lastLog && $lastLog->response_time > 2) {
                    $healthScore = 85;
                } else {
                    $healthScore = 100;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'status' => $service->last_status ?? 'UNKNOWN',
                    'health_score' => $healthScore,
                    'response_time' => $lastLog?->response_time,
                    'last_checked' => $lastLog?->created_at?->diffForHumans()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status kesehatan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * ============================================================
     *  🔍 API SEARCH SERVICES
     *  ============================================================
     *  🔗 URL: GET /api/services/search
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?q=kata_kunci&per_page=10&page=1
     * ============================================================
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $perPage = $request->input('per_page', 10);
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'total' => 0,
                        'from' => 0,
                        'to' => 0,
                        'current_page' => 1,
                        'last_page' => 1,
                        'prev_page_url' => null,
                        'next_page_url' => null
                    ]
                ]);
            }
            
            $services = Service::where('name', 'LIKE', "%{$query}%")
                ->orWhere('target', 'LIKE', "%{$query}%")
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            foreach ($services as $service) {
                $service->uptime = $this->calculateUptime($service->id, 30);
            }
            
            return response()->json([
                'success' => true,
                'data' => $services->items(),
                'pagination' => [
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem(),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'prev_page_url' => $services->previousPageUrl(),
                    'next_page_url' => $services->nextPageUrl()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: BULK DELETE SERVICES
     *  ============================================================
     *  🔗 URL: DELETE /api/services/bulk-delete
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "ids": [1, 2, 3] }
     * ============================================================
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada service yang dipilih'
                ], 400);
            }

            $deletedCount = Service::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} service",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE STATISTICS
     *  ============================================================
     *  🔗 URL: GET /api/services/stats
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function stats()
    {
        try {
            $totalServices = Service::count();
            $totalUp = Service::where('last_status', 'UP')->count();
            $totalWarning = Service::where('last_status', 'WARNING')->count();
            $totalDown = Service::where('last_status', 'DOWN')->count();
            $totalUnknown = Service::where('last_status', 'UNKNOWN')->count();

            $logsTotal = ServiceLog::count();
            $logsToday = ServiceLog::whereDate('created_at', today())->count();
            $logsThisWeek = ServiceLog::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => [
                        'total' => $totalServices,
                        'up' => $totalUp,
                        'warning' => $totalWarning,
                        'down' => $totalDown,
                        'unknown' => $totalUnknown,
                        'uptime_percentage' => $totalServices > 0 
                            ? round(($totalUp / $totalServices) * 100, 2) 
                            : 0,
                    ],
                    'logs' => [
                        'total' => $logsTotal,
                        'today' => $logsToday,
                        'this_week' => $logsThisWeek,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================================================
    // 🔧 PRIVATE HELPER METHODS
    // ================================================================

    private function fixTarget($target, $type)
    {
        if ($type === 'ping') {
            return trim($target);
        }
        
        if (!preg_match('/^https?:\/\/.+/', $target)) {
            return 'https://' . $target;
        }
        return $target;
    }

    private function getServiceStats($serviceId)
    {
        $logs = ServiceLog::where('service_id', $serviceId);

        $totalChecks = $logs->count();
        $upCount = $logs->where('status', 'UP')->count();
        $warningCount = $logs->where('status', 'WARNING')->count();
        $downCount = $logs->where('status', 'DOWN')->count();

        $avgResponseTime = $logs->avg('response_time');
        $maxResponseTime = $logs->max('response_time');
        $minResponseTime = $logs->min('response_time');

        $last24Hours = $logs->where('created_at', '>=', now()->subHours(24))->count();
        $last7Days = $logs->where('created_at', '>=', now()->subDays(7))->count();

        $last30Days = $logs->where('created_at', '>=', now()->subDays(30));
        $uptimeCount = $last30Days->where('status', 'UP')->count();
        $totalLast30Days = $last30Days->count();
        $uptimePercentage = $totalLast30Days > 0 
            ? round(($uptimeCount / $totalLast30Days) * 100, 2) 
            : 0;

        return [
            'total_checks' => $totalChecks,
            'up_count' => $upCount,
            'warning_count' => $warningCount,
            'down_count' => $downCount,
            'avg_response_time' => round($avgResponseTime ?? 0, 3),
            'max_response_time' => round($maxResponseTime ?? 0, 3),
            'min_response_time' => round($minResponseTime ?? 0, 3),
            'last_24_hours' => $last24Hours,
            'last_7_days' => $last7Days,
            'uptime_percentage_30d' => $uptimePercentage,
            'status_distribution' => [
                'UP' => $upCount,
                'WARNING' => $warningCount,
                'DOWN' => $downCount
            ]
        ];
    }

    private function calculateUptime($serviceId, $days = 30)
    {
        $logs = ServiceLog::where('service_id', $serviceId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $logs->count();
        
        if ($total === 0) {
            $service = Service::find($serviceId);
            if ($service) {
                if ($service->last_status === 'UP') return 100.00;
                elseif ($service->last_status === 'WARNING') return 70.00;
                elseif ($service->last_status === 'DOWN') return 0.00;
            }
            return 0.00;
        }

        $totalWeight = 0;
        foreach ($logs as $log) {
            if ($log->status === 'UP') {
                $totalWeight += 100;
            } elseif ($log->status === 'WARNING') {
                $totalWeight += 70;
            } elseif ($log->status === 'DOWN') {
                $totalWeight += 0;
            }
        }
        
        $uptime = round($totalWeight / $total, 2);
        return max(0, min(100, $uptime));
    }

    private function generateReportData($service, $logs, $dateFrom, $dateTo)
    {
        $totalChecks = $logs->count();
        $upCount = $logs->where('status', 'UP')->count();
        $warningCount = $logs->where('status', 'WARNING')->count();
        $downCount = $logs->where('status', 'DOWN')->count();

        $avgResponseTime = $logs->avg('response_time');
        $maxResponseTime = $logs->max('response_time');
        $minResponseTime = $logs->min('response_time');

        $logsByDate = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $criticalDates = [];
        $logsByDate->each(function($dateLogs, $date) use (&$criticalDates) {
            $hasDown = $dateLogs->where('status', 'DOWN')->isNotEmpty();
            $hasWarning = $dateLogs->where('status', 'WARNING')->isNotEmpty();
            if ($hasDown || $hasWarning) {
                $criticalDates[$date] = [
                    'total_checks' => $dateLogs->count(),
                    'down_count' => $dateLogs->where('status', 'DOWN')->count(),
                    'warning_count' => $dateLogs->where('status', 'WARNING')->count(),
                    'up_count' => $dateLogs->where('status', 'UP')->count()
                ];
            }
        });

        $issuesByHour = [];
        $logs->filter(function($log) {
            return in_array($log->status, ['DOWN', 'WARNING']);
        })->groupBy(function($log) {
            return $log->created_at->format('H');
        })->each(function($hourLogs, $hour) use (&$issuesByHour) {
            $issuesByHour[$hour] = [
                'total_issues' => $hourLogs->count(),
                'down_count' => $hourLogs->where('status', 'DOWN')->count(),
                'warning_count' => $hourLogs->where('status', 'WARNING')->count()
            ];
        });

        $recentDowns = $logs->where('status', 'DOWN')
            ->take(5)
            ->map(function($log) {
                return [
                    'time' => $log->created_at->format('Y-m-d H:i:s'),
                    'message' => $log->message,
                    'response_code' => $log->response_code
                ];
            });

        $uptimePercentage = $totalChecks > 0 
            ? round(($upCount / $totalChecks) * 100, 2) 
            : 0;

        return [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'target' => $service->target,
                'type' => $service->type,
                'last_status' => $service->last_status ?? 'UNKNOWN',
            ],
            'period' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_days' => (new \DateTime($dateTo))->diff(new \DateTime($dateFrom))->days + 1
            ],
            'statistics' => [
                'total_checks' => $totalChecks,
                'up_count' => $upCount,
                'warning_count' => $warningCount,
                'down_count' => $downCount,
                'uptime_percentage' => $uptimePercentage,
                'avg_response_time' => round($avgResponseTime ?? 0, 3),
                'max_response_time' => round($maxResponseTime ?? 0, 3),
                'min_response_time' => round($minResponseTime ?? 0, 3)
            ],
            'critical_dates' => $criticalDates,
            'vulnerable_hours' => $issuesByHour,
            'recent_downs' => $recentDowns,
            'logs' => $logs->map(function($log) {
                return [
                    'date' => $log->created_at->format('Y-m-d H:i:s'),
                    'status' => $log->status,
                    'response_code' => $log->response_code,
                    'response_time' => $log->response_time ? number_format($log->response_time, 3) : '-',
                    'message' => $log->message ?? '-'
                ];
            })
        ];
    }
}