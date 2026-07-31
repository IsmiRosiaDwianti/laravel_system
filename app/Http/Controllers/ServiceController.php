<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;
use Illuminate\Http\Request;
use App\Services\ServiceMonitorService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * 📋 Daftar Service
     */
    public function index(Request $request)
    {
        $waInterval = $request->input('wa_interval', session('wa_interval', 5));
        
        if ($request->has('wa_interval')) {
            session(['wa_interval' => $waInterval]);
            Service::query()->update(['wa_interval_minutes' => $waInterval]);
            Log::info("📝 WA Interval updated to {$waInterval} minutes for all services");
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['id', 'name', 'created_at', 'last_status', 'last_check_at'];
        $allowedOrders = ['asc', 'desc'];
        
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortOrder, $allowedOrders)) {
            $sortOrder = 'desc';
        }

        $showArchived = $request->input('show_archived', false);
        $perPage = $request->input('perPage', 10);
        
        $totalServices = Service::active()->count();
        $totalUp = Service::active()->where('last_status', 'UP')->count();
        $totalWarning = Service::active()->where('last_status', 'WARNING')->count();
        $totalDown = Service::active()->where('last_status', 'DOWN')->count();
        $totalArchived = Service::archived()->count();

        $query = Service::query();
        
        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }
        
        $services = $query->orderBy($sortBy, $sortOrder)
            ->paginate($perPage)
            ->appends([
                'perPage' => $perPage,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'show_archived' => $showArchived
            ]);
        
        foreach ($services as $service) {
            $service->uptime = $this->calculateUptime($service->id, 30);
        }
        
        if ($request->has('sort_by_uptime')) {
            $sortUptime = $request->input('sort_by_uptime', '');
            $sortUptimeOrder = $request->input('sort_uptime_order', 'desc');
            
            if ($sortUptime === 'uptime') {
                $allServices = $services->getCollection();
                $sorted = $allServices->sortBy(function($service) {
                    return $service->uptime;
                });
                
                if ($sortUptimeOrder === 'desc') {
                    $sorted = $sorted->reverse()->values();
                } else {
                    $sorted = $sorted->values();
                }
                
                $services->setCollection($sorted);
            }
        }
        
        return view('services', compact(
            'services', 
            'totalServices', 
            'totalUp', 
            'totalWarning', 
            'totalDown',
            'totalArchived',
            'perPage',
            'waInterval',
            'sortBy',
            'sortOrder',
            'showArchived'
        ));
    }

    public function create()
    {
        return redirect()->route('services');
    }

    /**
     * 🔧 Fix URL target berdasarkan type
     */
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

    /**
     * 💾 Simpan Service Baru
     */
    public function store(Request $request, ServiceMonitorService $monitor)
    {
        try {
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
            $validated['target'] = $this->fixTarget($validated['target'], $validated['type']);

            $existingTarget = Service::where('target', $validated['target'])->first();
            if ($existingTarget) {
                return redirect()->back()->withInput()->with('error', 'Target "' . $validated['target'] . '" sudah digunakan oleh service "' . $existingTarget->name . '"');
            }

            $existingName = Service::where('name', $validated['name'])->first();
            if ($existingName) {
                return redirect()->back()->withInput()->with('error', 'Nama service "' . $validated['name'] . '" sudah digunakan');
            }

            $service = Service::create([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'last_status' => 'UNKNOWN',
                'wa_interval_minutes' => $validated['wa_interval'] ?? 5,
                'is_archived' => false,
            ]);

            $monitor->check($service);
            $this->clearServiceCache();

            return redirect()->route('services')->with('success', 'Service "' . $service->name . '" berhasil ditambahkan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * ✏️ Edit Service (AJAX Support)
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        
        if ($service->is_archived) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service sedang diarsipkan, tidak dapat diedit'
                ], 403);
            }
            return redirect()->route('services', ['show_archived' => 1])
                ->with('error', 'Service sedang diarsipkan. Pulihkan terlebih dahulu untuk mengedit.');
        }
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $service
            ]);
        }
        
        return redirect()->route('services');
    }

    /**
     * 🔄 Update Service
     */
    public function update(Request $request, $id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);
            
            if ($service->is_archived) {
                return redirect()->route('services', ['show_archived' => 1])
                    ->with('error', 'Service sedang diarsipkan. Pulihkan terlebih dahulu untuk mengupdate.');
            }

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
            $validated['target'] = $this->fixTarget($validated['target'], $validated['type']);

            $existingTarget = Service::where('target', $validated['target'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingTarget) {
                return redirect()->back()->withInput()->with('error', 'Target "' . $validated['target'] . '" sudah digunakan oleh service "' . $existingTarget->name . '"');
            }

            $existingName = Service::where('name', $validated['name'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingName) {
                return redirect()->back()->withInput()->with('error', 'Nama service "' . $validated['name'] . '" sudah digunakan');
            }

            $service->update([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'wa_interval_minutes' => $validated['wa_interval'] ?? $service->wa_interval_minutes,
            ]);

            $monitor->check($service);
            $this->clearServiceCache();

            return redirect()->route('services')->with('success', 'Service "' . $service->name . '" berhasil diupdate');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * 📦 Archive / Unarchive Service
     */
    public function archive($id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            $service->update(['is_archived' => true]);
            
            Log::info("📦 Service {$serviceName} diarsipkan");
            $this->clearServiceCache();
            
            return redirect()->route('services')->with('success', 'Service "' . $serviceName . '" berhasil diarsipkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengarsipkan service: ' . $e->getMessage());
        }
    }

    public function unarchive($id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            $service->update(['is_archived' => false]);
            
            Log::info("📦 Service {$serviceName} dipulihkan dari arsip");
            $this->clearServiceCache();
            
            return redirect()->route('services', ['show_archived' => 1])
                ->with('success', 'Service "' . $serviceName . '" berhasil dipulihkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memulihkan service: ' . $e->getMessage());
        }
    }

    /**
     * 🗑️ Delete Service
     */
    public function forceDelete($id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            
            if (!$service->is_archived) {
                return redirect()->back()->with('error', 'Service harus diarsipkan terlebih dahulu sebelum dihapus permanen');
            }
            
            ServiceLog::where('service_id', $id)->delete();
            $service->delete();
            
            Log::info("🗑️ Service {$serviceName} dihapus permanen");
            $this->clearServiceCache();
            
            return redirect()->route('services', ['show_archived' => 1])
                ->with('success', 'Service "' . $serviceName . '" berhasil dihapus permanen');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus service: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            $service->delete();
            
            Log::info("🗑️ Service {$serviceName} dihapus");
            $this->clearServiceCache();
            
            return redirect()->route('services')->with('success', 'Service "' . $serviceName . '" berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * 📋 Detail Service (AJAX Support)
     */
    public function detail($id)
    {
        try {
            $service = Service::findOrFail($id);

            if (request()->ajax()) {
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
                        'last_check_at' => $service->last_check_at?->format('Y-m-d H:i:s'),
                        'is_archived' => $service->is_archived,
                        'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                    ]
                ]);
            }

            return view('services-detail', compact('service'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan: ' . $e->getMessage()
                ], 404);
            }
            return redirect()->route('services')->with('error', 'Service tidak ditemukan');
        }
    }

    /**
     * 📈 Hitung Uptime
     */
    public function calculateUptime($serviceId, $days = 30)
    {
        $service = Service::find($serviceId);
        
        $logs = ServiceLog::where('service_id', $serviceId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $logs->count();
        
        if ($total === 0) {
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

    /**
     * 📋 Get Service Logs
     */
    public function logs($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            
            if ($service->is_archived) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service sedang diarsipkan. Pulihkan terlebih dahulu untuk melihat log.',
                        'is_archived' => true
                    ], 403);
                }
                return redirect()->route('services', ['show_archived' => 1])
                    ->with('error', 'Service sedang diarsipkan. Pulihkan terlebih dahulu untuk melihat log.');
            }
            
            $perPage = $request->input('perPage', 20);
            $status = $request->input('status');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $onlyChanges = $request->input('only_changes', false);

            $query = ServiceLog::where('service_id', $id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom && $dateTo) {
                $startDate = Carbon::parse($dateFrom)->startOfDay();
                $endDate = Carbon::parse($dateTo)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } elseif ($dateFrom) {
                $startDate = Carbon::parse($dateFrom)->startOfDay();
                $query->where('created_at', '>=', $startDate);
            } elseif ($dateTo) {
                $endDate = Carbon::parse($dateTo)->endOfDay();
                $query->where('created_at', '<=', $endDate);
            }

            if ($onlyChanges) {
                $query->where('is_status_change', true);
            }

            $logs = $query->latest()
                ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'only_changes' => $onlyChanges
                ]);

            $totalChanges = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->count();

            $changesLast7Days = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $logs->items(),
                    'pagination' => [
                        'total' => $logs->total(),
                        'per_page' => $logs->perPage(),
                        'current_page' => $logs->currentPage(),
                        'last_page' => $logs->lastPage()
                    ],
                    'statistics' => [
                        'total_changes' => $totalChanges,
                        'changes_last_7_days' => $changesLast7Days,
                    ],
                    'is_archived' => false
                ]);
            }

            return view('services-logs', compact(
                'service', 
                'logs', 
                'status', 
                'dateFrom', 
                'dateTo',
                'onlyChanges',
                'totalChanges',
                'changesLast7Days'
            ));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data logs: ' . $e->getMessage()
                ], 404);
            }
            return redirect()->route('services')->with('error', 'Service tidak ditemukan');
        }
    }

    /**
     * 🔄 Force Check Service
     */
    public function check($id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);
            $monitor->check($service);
            $service->refresh();

            $this->clearServiceCache();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service "' . $service->name . '" berhasil di-check',
                    'data' => [
                        'status' => $service->last_status ?? 'UNKNOWN',
                        'response_code' => $service->last_code ?? 'N/A',
                        'response_time' => $service->last_response_time ?? 0,
                        'message' => $service->last_message ?? '-',
                        'checked_at' => $service->last_check_at?->format('Y-m-d H:i:s'),
                        'is_archived' => $service->is_archived,
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Service "' . $service->name . '" berhasil di-check');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal check service: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal check service: ' . $e->getMessage());
        }
    }

    /**
     * 📊 Service Overview
     */
    public function overview()
    {
        try {
            $totalServices = Service::active()->count();
            $totalUp = Service::active()->where('last_status', 'UP')->count();
            $totalWarning = Service::active()->where('last_status', 'WARNING')->count();
            $totalDown = Service::active()->where('last_status', 'DOWN')->count();
            $totalUnknown = Service::active()->where('last_status', 'UNKNOWN')->count();
            $totalArchived = Service::archived()->count();

            $servicesByType = Service::active()
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();

            $recentIssues = ServiceLog::where('status', 'DOWN')
                ->orWhere('status', 'WARNING')
                ->whereHas('service', function($query) {
                    $query->active();
                })
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

            $response = [
                'success' => true,
                'data' => [
                    'total' => $totalServices,
                    'up' => $totalUp,
                    'warning' => $totalWarning,
                    'down' => $totalDown,
                    'unknown' => $totalUnknown,
                    'archived' => $totalArchived,
                    'uptime_percentage' => $totalServices > 0 ? round(($totalUp / $totalServices) * 100, 2) : 0,
                    'services_by_type' => $servicesByType,
                    'recent_issues' => $recentIssues
                ]
            ];

            if (request()->ajax()) {
                return response()->json($response);
            }

            return view('dashboard-overview', compact(
                'totalServices',
                'totalUp',
                'totalWarning',
                'totalDown',
                'totalUnknown',
                'totalArchived',
                'servicesByType',
                'recentIssues'
            ));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil overview: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('dashboard')->with('error', 'Gagal mengambil data overview');
        }
    }

    /**
     * 📤 Export CSV
     */
    public function export()
    {
        try {
            $services = Service::with(['logs' => function($query) {
                $query->latest()->limit(1);
            }])->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="services_' . date('Y-m-d') . '.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($services) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");

                fputcsv($file, [
                    'ID', 'Nama Service', 'Target', 'Type', 'Status Terakhir',
                    'Response Code', 'Response Time (s)', 'Message',
                    'Terakhir Diperiksa', 'Dibuat Pada', 'Diupdate Pada'
                ]);

                foreach ($services as $service) {
                    $latestLog = $service->logs->first();
                    fputcsv($file, [
                        $service->id,
                        $service->name,
                        $service->target,
                        $service->type ?? 'http',
                        $service->last_status ?? 'UNKNOWN',
                        $latestLog?->response_code ?? '-',
                        $latestLog?->response_time ? number_format($latestLog->response_time, 3) : '-',
                        $latestLog?->message ?? '-',
                        $latestLog?->created_at?->format('Y-m-d H:i:s') ?? '-',
                        $service->created_at?->format('Y-m-d H:i:s'),
                        $service->updated_at?->format('Y-m-d H:i:s')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * 🗑️ Bulk Delete
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
            $this->clearServiceCache();

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
     * 💚 Health Status
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
     * 📄 Download PDF Report
     */
    public function downloadReport($id, Request $request)
    {
        try {
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf') && !class_exists('Barryvdh\DomPDF\PDF')) {
                throw new \Exception('DomPDF tidak terinstall. Jalankan: composer require barryvdh/laravel-dompdf');
            }

            $service = Service::findOrFail($id);
            
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            
            Log::info('📄 DOWNLOAD REPORT - Request:', [
                'service_id' => $id,
                'service_name' => $service->name,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'all_params' => $request->all()
            ]);
            
            if (!$dateFrom || !$dateTo) {
                $dateFrom = $service->created_at->format('Y-m-d');
                $dateTo = now()->format('Y-m-d');
                Log::info('📄 Using default dates:', ['date_from' => $dateFrom, 'date_to' => $dateTo]);
            }
            
            $minDate = $service->created_at->format('Y-m-d');
            if ($dateFrom < $minDate) {
                $dateFrom = $minDate;
                Log::info('📄 Adjusted date_from to minDate:', ['date_from' => $dateFrom]);
            }
            
            $maxDate = now()->format('Y-m-d');
            if ($dateTo > $maxDate) {
                $dateTo = $maxDate;
                Log::info('📄 Adjusted date_to to maxDate:', ['date_to' => $dateTo]);
            }
            
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
            
            $logs = ServiceLog::where('service_id', $id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'asc')
                ->get();
            
            Log::info('📄 Logs found:', [
                'count' => $logs->count(),
                'date_from' => $startDate->format('Y-m-d H:i:s'),
                'date_to' => $endDate->format('Y-m-d H:i:s')
            ]);

            if ($logs->isEmpty()) {
                $message = "Tidak ada data pada periode {$dateFrom} sampai {$dateTo}. ";
                $message .= "Service ini baru aktif sejak {$service->created_at->format('d-m-Y H:i')}";
                
                Log::warning('📄 No data found:', ['message' => $message]);
                
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 404);
                }
                
                return redirect()
                    ->back()
                    ->with('warning', $message);
            }

            $reportData = $this->generateReportData($service, $logs, $dateFrom, $dateTo);
            return $this->exportPdfReport($reportData, $service);

        } catch (\Exception $e) {
            Log::error('📄 Download Report Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat laporan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal membuat laporan: ' . $e->getMessage());
        }
    }

    /**
     * 📊 Generate Report Data
     */
    private function generateReportData($service, $logs, $dateFrom, $dateTo)
    {
        $totalChecks = $logs->count();
        $upCount = $logs->where('status', 'UP')->count();
        $warningCount = $logs->where('status', 'WARNING')->count();
        $downCount = $logs->where('status', 'DOWN')->count();

        $avgResponseTime = $logs->avg('response_time');
        $maxResponseTime = $logs->max('response_time');
        $minResponseTime = $logs->min('response_time');

        $start = new \DateTime($dateFrom);
        $end = new \DateTime($dateTo);
        $requestedDays = $start->diff($end)->days + 1;

        $uptimePercentage = $totalChecks > 0 ? round(($upCount / $totalChecks) * 100, 2) : 0;
        $downPercentage = $totalChecks > 0 ? round(($downCount / $totalChecks) * 100, 2) : 0;
        $warningPercentage = $totalChecks > 0 ? round(($warningCount / $totalChecks) * 100, 2) : 0;
        
        $totalPct = $uptimePercentage + $downPercentage + $warningPercentage;
        if ($totalPct > 0 && $totalPct != 100) {
            $diff = round(100 - $totalPct, 2);
            if ($uptimePercentage >= $downPercentage && $uptimePercentage >= $warningPercentage) {
                $uptimePercentage = round($uptimePercentage + $diff, 2);
            } elseif ($downPercentage >= $warningPercentage) {
                $downPercentage = round($downPercentage + $diff, 2);
            } else {
                $warningPercentage = round($warningPercentage + $diff, 2);
            }
        }

        $dailyStats = $logs->groupBy(function($log) {
            return $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d');
        })->map(function($dayLogs) {
            return [
                'date' => $dayLogs->first()->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d'),
                'total' => $dayLogs->count(),
                'up' => $dayLogs->where('status', 'UP')->count(),
                'warning' => $dayLogs->where('status', 'WARNING')->count(),
                'down' => $dayLogs->where('status', 'DOWN')->count(),
                'avg_response_time' => round($dayLogs->avg('response_time') ?? 0, 3),
                'uptime' => $dayLogs->count() > 0 ? round(($dayLogs->where('status', 'UP')->count() / $dayLogs->count()) * 100, 2) : 0,
            ];
        })->values();

        $criticalDates = $dailyStats->filter(function($day) {
            return $day['down'] > 0 || $day['warning'] > 0;
        })->mapWithKeys(function($day) {
            return [$day['date'] => [
                'total_checks' => $day['total'],
                'up_count' => $day['up'],
                'warning_count' => $day['warning'],
                'down_count' => $day['down'],
                'uptime' => $day['uptime']
            ]];
        })->toArray();

        $recentDowns = $logs->where('status', 'DOWN')
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function($log) {
                return [
                    'time' => $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    'message' => $log->message ?? '-',
                    'response_code' => $log->response_code ?? '-'
                ];
            });

        $logsCollection = $logs->sortByDesc('created_at')->take(100)->map(function($log) {
            return [
                'date' => $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                'status' => $log->status ?? 'UNKNOWN',
                'response_code' => $log->response_code ?? '-',
                'response_time' => $log->response_time ? number_format($log->response_time, 3) : '-',
                'message' => $log->message ?? '-'
            ];
        })->sortBy('date')->values();

        return [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'target' => $service->target,
                'type' => $service->type,
                'last_status' => $service->last_status ?? 'UNKNOWN',
                'created_at' => $service->created_at?->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            ],
            'period' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_days' => (new \DateTime($dateTo))->diff(new \DateTime($dateFrom))->days + 1
            ],
            'requested_days' => $requestedDays,
            'statistics' => [
                'total_checks' => $totalChecks,
                'up_count' => $upCount,
                'warning_count' => $warningCount,
                'down_count' => $downCount,
                'uptime_percentage' => $uptimePercentage,
                'down_percentage' => $downPercentage,
                'warning_percentage' => $warningPercentage,
                'avg_response_time' => round($avgResponseTime ?? 0, 3),
                'max_response_time' => round($maxResponseTime ?? 0, 3),
                'min_response_time' => round($minResponseTime ?? 0, 3),
            ],
            'daily_stats' => $dailyStats,
            'critical_dates' => $criticalDates,
            'recent_downs' => $recentDowns,
            'logs' => $logsCollection,
            'total_logs_available' => $totalChecks,
            'logs_displayed' => min(100, $totalChecks),
        ];
    }

    /**
     * 📄 Export PDF
     */
    private function exportPdfReport($reportData, $service)
    {
        try {
            $filename = 'laporan_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $service->name) 
                . '_' . $reportData['period']['date_from'] . '_to_' . $reportData['period']['date_to'] . '.pdf';
            
            if (!view()->exists('reports.service-pdf')) {
                throw new \Exception('View reports.service-pdf tidak ditemukan.');
            }

            $pdf = Pdf::loadView('reports.service-pdf', compact('reportData'));
            $pdf->setPaper('A4', 'portrait');
            return $pdf->download($filename);
        } catch (\Exception $e) {
            throw new \Exception('Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 Search Services (AJAX)
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
     * 📊 Status Change Statistics
     */
    public function statusChanges($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            $days = $request->input('days', 7);
            
            $changes = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($log) {
                    return [
                        'id' => $log->id,
                        'old_status' => $log->previous_status,
                        'new_status' => $log->status,
                        'changed_at' => $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                        'message' => $log->message,
                    ];
                });
            
            $totalChanges = $changes->count();
            
            $statusCounts = [
                'UP_TO_DOWN' => $changes->where('old_status', 'UP')->where('new_status', 'DOWN')->count(),
                'DOWN_TO_UP' => $changes->where('old_status', 'DOWN')->where('new_status', 'UP')->count(),
                'UP_TO_WARNING' => $changes->where('old_status', 'UP')->where('new_status', 'WARNING')->count(),
                'WARNING_TO_UP' => $changes->where('old_status', 'WARNING')->where('new_status', 'UP')->count(),
                'WARNING_TO_DOWN' => $changes->where('old_status', 'WARNING')->where('new_status', 'DOWN')->count(),
                'DOWN_TO_WARNING' => $changes->where('old_status', 'DOWN')->where('new_status', 'WARNING')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name,
                    ],
                    'period' => $days . ' days',
                    'total_changes' => $totalChanges,
                    'status_counts' => $statusCounts,
                    'changes' => $changes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data perubahan status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 Get Service Data for AJAX Polling
     * 🔥 DIPERBAIKI: Menghapus filter last_update yang menyebabkan data hilang
     */
    public function getServiceData(Request $request)
    {
        try {
            $perPage = $request->input('perPage', 10);
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $showArchived = $request->input('show_archived', false);
            $lastUpdate = $request->input('last_update', 0);
            
            // 🔥 VALIDASI PERPAGE - Pastikan tidak 0 atau null
            $perPage = max(1, intval($perPage));
            
            $allowedSorts = ['id', 'name', 'created_at', 'last_status', 'last_check_at'];
            $allowedOrders = ['asc', 'desc'];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }
            if (!in_array($sortOrder, $allowedOrders)) {
                $sortOrder = 'desc';
            }
            
            $query = Service::query();
            
            // 🔥 FILTER ARCHIVED
            if ($showArchived) {
                $query->archived();
            } else {
                $query->active();
            }
            
            // 🔥 PERBAIKAN: HAPUS FILTER last_update
            // Karena filter ini menyebabkan data hilang jika tidak ada perubahan
            // Comment atau hapus bagian ini:
            /*
            if ($lastUpdate > 0) {
                $lastUpdateDate = Carbon::createFromTimestamp($lastUpdate);
                $query->where('updated_at', '>=', $lastUpdateDate);
            }
            */
            
            // 🔥 ALTERNATIF: Simpan last_update untuk response saja
            $hasChanges = false;
            if ($lastUpdate > 0) {
                $lastUpdateDate = Carbon::createFromTimestamp($lastUpdate);
                // Cek apakah ada perubahan tanpa memfilter query
                $changedCount = Service::where('updated_at', '>=', $lastUpdateDate)->count();
                $hasChanges = $changedCount > 0;
            }
            
            // 🔥 PAGINATION
            $services = $query->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
            
            // 🔥 BUILD SERVICE DATA DENGAN UPTIME
            $serviceData = [];
            foreach ($services as $service) {
                $uptime = $this->calculateUptime($service->id, 30);
                
                $serviceData[] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'target' => $service->target,
                    'type' => $service->type,
                    'last_status' => $service->last_status ?? 'UNKNOWN',
                    'last_code' => $service->last_code ?? '-',
                    'last_response_time' => $service->last_response_time ?? 0,
                    'last_message' => $service->last_message ?? '-',
                    'last_action' => $service->last_action ?? '-',
                    'last_check_at' => $service->last_check_at?->format('Y-m-d H:i:s'),
                    'uptime' => $uptime,
                    'is_archived' => $service->is_archived,
                    'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                    'wa_interval_minutes' => $service->wa_interval_minutes,
                ];
            }
            
            // 🔥 STATISTIK
            $totalServices = Service::active()->count();
            $totalUp = Service::active()->where('last_status', 'UP')->count();
            $totalWarning = Service::active()->where('last_status', 'WARNING')->count();
            $totalDown = Service::active()->where('last_status', 'DOWN')->count();
            $totalArchived = Service::archived()->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $serviceData,
                    'statistics' => [
                        'total' => $totalServices,
                        'up' => $totalUp,
                        'warning' => $totalWarning,
                        'down' => $totalDown,
                        'archived' => $totalArchived,
                        'uptime_percentage' => $totalServices > 0 ? round(($totalUp / $totalServices) * 100, 2) : 0,
                    ],
                    'pagination' => [
                        'total' => $services->total(),
                        'per_page' => $services->perPage(),
                        'current_page' => $services->currentPage(),
                        'last_page' => $services->lastPage(),
                        'from' => $services->firstItem(),
                        'to' => $services->lastItem(),
                    ],
                    'timestamp' => now()->timestamp,
                    'has_changes' => $hasChanges,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('getServiceData error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔄 Get Service Updates (Only Changes)
     */
    public function getServiceUpdates(Request $request)
    {
        try {
            $lastUpdate = $request->input('last_update', 0);
            $limit = $request->input('limit', 50);
            
            if ($lastUpdate == 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'services' => [],
                        'has_changes' => false,
                        'timestamp' => now()->timestamp,
                    ]
                ]);
            }
            
            $lastUpdateDate = Carbon::createFromTimestamp($lastUpdate);
            
            $changedServices = Service::where('updated_at', '>=', $lastUpdateDate)
                ->active()
                ->limit($limit)
                ->get();
            
            $problemServices = Service::whereIn('last_status', ['DOWN', 'WARNING'])
                ->where('updated_at', '<', $lastUpdateDate)
                ->active()
                ->limit($limit - $changedServices->count())
                ->get();
            
            $services = $changedServices->merge($problemServices)->unique('id');
            
            $serviceData = [];
            foreach ($services as $service) {
                $serviceData[] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'last_status' => $service->last_status ?? 'UNKNOWN',
                    'last_response_time' => $service->last_response_time ?? 0,
                    'uptime' => $this->calculateUptime($service->id, 30),
                    'last_check_at' => $service->last_check_at?->format('Y-m-d H:i:s'),
                    'is_changed' => $changedServices->contains('id', $service->id),
                    'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                ];
            }
            
            $stats = [
                'up' => Service::active()->where('last_status', 'UP')->count(),
                'warning' => Service::active()->where('last_status', 'WARNING')->count(),
                'down' => Service::active()->where('last_status', 'DOWN')->count(),
                'total' => Service::active()->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $serviceData,
                    'statistics' => $stats,
                    'timestamp' => now()->timestamp,
                    'has_changes' => $services->isNotEmpty(),
                    'changed_ids' => $changedServices->pluck('id')->toArray(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ⚡ Get Service Statuses (Lightweight with Cache)
     */
    public function getServiceStatuses(Request $request)
    {
        try {
            $showArchived = $request->input('show_archived', false);
            $cacheKey = 'service_statuses_' . md5($showArchived ? 'archived' : 'active');
            
            $data = Cache::remember($cacheKey, 30, function() use ($showArchived) {
                $services = Service::query();
                
                if ($showArchived) {
                    $services->archived();
                } else {
                    $services->active();
                }
                
                $services = $services->select([
                    'id', 'name', 'last_status', 'last_response_time', 
                    'last_check_at', 'updated_at', 'is_archived'
                ])->get();
                
                $serviceData = [];
                foreach ($services as $service) {
                    $serviceData[] = [
                        'id' => $service->id,
                        'name' => $service->name,
                        'status' => $service->last_status ?? 'UNKNOWN',
                        'response_time' => $service->last_response_time ?? 0,
                        'last_check' => $service->last_check_at?->format('Y-m-d H:i:s'),
                        'is_archived' => $service->is_archived,
                    ];
                }
                
                $stats = [
                    'total' => $services->count(),
                    'up' => $services->where('last_status', 'UP')->count(),
                    'warning' => $services->where('last_status', 'WARNING')->count(),
                    'down' => $services->where('last_status', 'DOWN')->count(),
                ];
                
                return [
                    'services' => $serviceData,
                    'statistics' => $stats,
                    'timestamp' => now()->timestamp,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🗑️ Clear Service Cache
     */
    public function clearServiceCache()
    {
        try {
            $keys = Cache::get('service_cache_keys', []);
            foreach ($keys as $key) {
                Cache::forget($key);
            }
            Cache::forget('service_cache_keys');
            Cache::forget('service_statuses_active');
            Cache::forget('service_statuses_archived');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache service berhasil dibersihkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache: ' . $e->getMessage()
            ], 500);
        }
    }
}