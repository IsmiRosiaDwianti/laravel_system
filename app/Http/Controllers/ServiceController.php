<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;
use Illuminate\Http\Request;
use App\Services\ServiceMonitorService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        
        $totalServices = Service::count();
        $totalUp = Service::where('last_status', 'UP')->count();
        $totalWarning = Service::where('last_status', 'WARNING')->count();
        $totalDown = Service::where('last_status', 'DOWN')->count();
        
        $services = Service::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);
        
        // Hitung uptime untuk setiap service
        foreach ($services as $service) {
            $service->uptime = $this->calculateUptime($service->id, 30);
        }
        
        return view('services', compact(
            'services', 
            'totalServices', 
            'totalUp', 
            'totalWarning', 
            'totalDown'
        ));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        return redirect()->route('services');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request, ServiceMonitorService $monitor)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'target' => 'required|string|max:255',
                'type' => ['required', Rule::in(['http', 'https', 'ping', 'port'])],
            ]);

            // ✅ CEK APAKAH TARGET SUDAH ADA
            $existingTarget = Service::where('target', $validated['target'])->first();
            if ($existingTarget) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Target URL/IP "' . $validated['target'] . '" sudah digunakan oleh service "' . $existingTarget->name . '"');
            }

            // ✅ CEK APAKAH NAMA SUDAH ADA
            $existingName = Service::where('name', $validated['name'])->first();
            if ($existingName) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Nama service "' . $validated['name'] . '" sudah digunakan');
            }

            $service = Service::create([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'last_status' => 'UNKNOWN'
            ]);

            $monitor->check($service);

            return redirect()
                ->route('services')
                ->with('success', 'Service "' . $service->name . '" berhasil ditambahkan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $service
            ]);
        }
        
        return redirect()->route('services');
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, $id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'target' => 'required|string|max:255',
                'type' => ['required', Rule::in(['http', 'https', 'ping', 'port'])],
            ]);

            // ✅ CEK APAKAH TARGET SUDAH ADA (kecuali dirinya sendiri)
            $existingTarget = Service::where('target', $validated['target'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingTarget) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Target URL/IP "' . $validated['target'] . '" sudah digunakan oleh service "' . $existingTarget->name . '"');
            }

            // ✅ CEK APAKAH NAMA SUDAH ADA (kecuali dirinya sendiri)
            $existingName = Service::where('name', $validated['name'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingName) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Nama service "' . $validated['name'] . '" sudah digunakan');
            }

            $service->update([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type']
            ]);

            $monitor->check($service);

            return redirect()
                ->route('services')
                ->with('success', 'Service "' . $service->name . '" berhasil diupdate');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            $service->delete();

            return redirect()
                ->route('services')
                ->with('success', 'Service "' . $serviceName . '" berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get detailed information of a service.
     * 🔥 DIPERBAIKI: Menambahkan action/tindakan ke response
     */
    public function detail($id)
    {
        try {
            $service = Service::with(['logs' => function($query) {
                $query->latest()->limit(10);
            }])->findOrFail($id);

            $latestLog = $service->logs()->latest()->first();
            $stats = $this->getServiceStats($service->id);

            // 🔥 AMBIL ACTION DARI LOG TERAKHIR
            $action = $latestLog?->action ?? '-';

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'target' => $service->target,
                        'type' => $service->type,
                        'last_status' => $service->last_status ?? 'UNKNOWN',
                        'last_response_code' => $latestLog?->response_code,
                        'last_response_time' => $latestLog?->response_time,
                        'last_message' => $latestLog?->message ?? '-',
                        'last_action' => $action, // 🔥 TAMBAHKAN
                        'last_checked_at' => $latestLog?->created_at?->format('d/m/Y H:i:s') ?? $service->updated_at?->format('d/m/Y H:i:s'),
                        'created_at' => $service->created_at?->format('d/m/Y H:i:s'),
                        'updated_at' => $service->updated_at?->format('d/m/Y H:i:s'),
                        'stats' => $stats,
                        'recent_logs' => $service->logs()->latest()->limit(5)->get()->map(function($log) {
                            return [
                                'status' => $log->status,
                                'response_code' => $log->response_code,
                                'response_time' => $log->response_time,
                                'message' => $log->message,
                                'action' => $log->action ?? '-', // 🔥 TAMBAHKAN
                                'created_at' => $log->created_at->format('d/m/Y H:i:s')
                            ];
                        })
                    ]
                ]);
            }

            return view('services-detail', compact('service', 'latestLog', 'stats', 'action'));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan atau terjadi kesalahan: ' . $e->getMessage()
                ], 404);
            }
            
            return redirect()
                ->route('services')
                ->with('error', 'Service tidak ditemukan');
        }
    }

    /**
     * Get statistics for a specific service.
     */
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

    /**
     * Calculate uptime percentage for a specific service.
     * Menggunakan bobot: UP=100, WARNING=70, DOWN=0
     * 
     * @param int $serviceId
     * @param int $days
     * @return float
     */
    public function calculateUptime($serviceId, $days = 30)
    {
        $logs = ServiceLog::where('service_id', $serviceId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $logs->count();
        
        // Jika tidak ada log sama sekali, cek status terakhir
        if ($total === 0) {
            $service = Service::find($serviceId);
            if ($service) {
                if ($service->last_status === 'UP') return 100.00;
                elseif ($service->last_status === 'WARNING') return 70.00;
                elseif ($service->last_status === 'DOWN') return 0.00;
            }
            return 0.00;
        }

        // ✅ HITUNG DENGAN BOBOT
        // UP = 100, WARNING = 70, DOWN = 0
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
        
        // Minimal 0, maksimal 100
        return max(0, min(100, $uptime));
    }

    /**
     * Get service logs history.
     */
    public function logs($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            
            $perPage = $request->input('perPage', 20);
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

            $logs = $query->latest()
                ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $logs->items(),
                    'pagination' => [
                        'total' => $logs->total(),
                        'per_page' => $logs->perPage(),
                        'current_page' => $logs->currentPage(),
                        'last_page' => $logs->lastPage()
                    ]
                ]);
            }

            return view('services-logs', compact('service', 'logs', 'status', 'dateFrom', 'dateTo'));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data logs: ' . $e->getMessage()
                ], 404);
            }

            return redirect()
                ->route('services')
                ->with('error', 'Service tidak ditemukan');
        }
    }

    /**
     * Force check a service.
     */
    public function check($id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);
            $monitor->check($service);

            $latestLog = $service->logs()->latest()->first();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service "' . $service->name . '" berhasil di-check',
                    'data' => [
                        'status' => $service->last_status,
                        'response_code' => $latestLog?->response_code,
                        'response_time' => $latestLog?->response_time,
                        'message' => $latestLog?->message,
                        'checked_at' => $latestLog?->created_at?->format('d/m/Y H:i:s')
                    ]
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Service "' . $service->name . '" berhasil di-check');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal check service: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal check service: ' . $e->getMessage());
        }
    }

    /**
     * Get service status overview for dashboard.
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

            $response = [
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

            return redirect()
                ->route('dashboard')
                ->with('error', 'Gagal mengambil data overview');
        }
    }

    /**
     * Export services data to CSV.
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
                    'ID',
                    'Nama Service',
                    'Target',
                    'Type',
                    'Status Terakhir',
                    'Response Code',
                    'Response Time (s)',
                    'Message',
                    'Terakhir Diperiksa',
                    'Dibuat Pada',
                    'Diupdate Pada'
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
            return redirect()
                ->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete services.
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
     * Get service health status.
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
     * Download PDF report for a specific service.
     */
    public function downloadReport($id, Request $request)
    {
        try {
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf') && !class_exists('Barryvdh\DomPDF\PDF')) {
                throw new \Exception('DomPDF tidak terinstall. Jalankan: composer require barryvdh/laravel-dompdf');
            }

            $service = Service::findOrFail($id);
            
            $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            $logs = ServiceLog::where('service_id', $id)
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->orderBy('created_at', 'asc')
                ->get();

            $reportData = $this->generateReportData($service, $logs, $dateFrom, $dateTo);

            return $this->exportPdfReport($reportData, $service);

        } catch (\Exception $e) {
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
     * Generate report data for a service.
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
                'last_status' => $service->last_status ?? 'UNKNOWN'
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

    /**
     * Export report as PDF.
     */
    private function exportPdfReport($reportData, $service)
    {
        try {
            $filename = 'laporan_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $service->name) 
                . '_' . $reportData['period']['date_from'] . '_to_' . $reportData['period']['date_to'] . '.pdf';
            
            if (!view()->exists('reports.service-pdf')) {
                throw new \Exception('View reports.service-pdf tidak ditemukan. Buat file di resources/views/reports/service-pdf.blade.php');
            }

            $pdf = Pdf::loadView('reports.service-pdf', compact('reportData'));
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            throw new \Exception('Gagal generate PDF: ' . $e->getMessage());
        }
    }
}