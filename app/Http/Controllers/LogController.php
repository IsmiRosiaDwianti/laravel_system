<?php

namespace App\Http\Controllers;

use App\Models\ServiceLog;
use App\Models\SmokeLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * Display a listing of the logs.
     */
    public function index(Request $request)
    {
        // ==================== QUERY LOGS ====================
        $query = ServiceLog::with('service');
        
        // Filter berdasarkan service
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%")
                  ->orWhere('response_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function($subq) use ($search) {
                      $subq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // ==================== STATISTIK (SEBELUM PAGINATE) ====================
        $statsQuery = clone $query;
        $stats = [
            'total' => $statsQuery->count(),
            'up' => (clone $statsQuery)->where('status', 'UP')->count(),
            'warning' => (clone $statsQuery)->where('status', 'WARNING')->count(),
            'down' => (clone $statsQuery)->where('status', 'DOWN')->count(),
            'unknown' => (clone $statsQuery)->where('status', 'UNKNOWN')->count(),
        ];
        
        // ==================== PAGINATION ====================
        $logs = $query->latest('created_at')
                     ->paginate($request->per_page ?? 10)
                     ->withQueryString();
        
        // ==================== AMBIL SERVICE UNTUK FILTER ====================
        $services = Service::orderBy('name')->get();
        
        // ==================== KIRIM KE VIEW ====================
        return view('logs', compact('logs', 'stats', 'services'));
    }
    
    /**
     * Get statistics for logs.
     */
    private function getStats()
    {
        return [
            'total' => ServiceLog::count(),
            'up' => ServiceLog::where('status', 'UP')->count(),
            'warning' => ServiceLog::where('status', 'WARNING')->count(),
            'down' => ServiceLog::where('status', 'DOWN')->count(),
            'unknown' => ServiceLog::where('status', 'UNKNOWN')->count(),
            'today' => ServiceLog::whereDate('created_at', today())->count(),
            'this_week' => ServiceLog::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];
    }
    
    /**
     * Get count of logs where status changed.
     */
    private function getStatusChangedCount()
    {
        return ServiceLog::whereIn('id', function($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('service_logs as sl1')
                ->whereExists(function($exists) {
                    $exists->select(DB::raw(1))
                        ->from('service_logs as sl2')
                        ->whereColumn('sl2.service_id', 'sl1.service_id')
                        ->whereColumn('sl2.created_at', '<', 'sl1.created_at')
                        ->whereRaw('sl2.status != sl1.status');
                });
        })->count();
    }
    
    /**
     * Show specific log details.
     */
    public function show($id)
    {
        $log = ServiceLog::with('service')->findOrFail($id);
        
        $previousLog = ServiceLog::where('service_id', $log->service_id)
                               ->where('id', '<', $id)
                               ->latest()
                               ->first();
        
        $nextLog = ServiceLog::where('service_id', $log->service_id)
                           ->where('id', '>', $id)
                           ->oldest()
                           ->first();
        
        $statusHistory = ServiceLog::where('service_id', $log->service_id)
                                 ->select('status', 'created_at', 'id')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(10)
                                 ->get();
        
        return view('logs-detail', compact(
            'log',
            'previousLog',
            'nextLog',
            'statusHistory'
        ));
    }
    
    /**
     * Get status change history for a service.
     */
    public function getStatusHistory($serviceId)
    {
        $logs = ServiceLog::where('service_id', $serviceId)
                         ->orderBy('created_at', 'asc')
                         ->get();
        
        $changes = [];
        $previousStatus = null;
        
        foreach ($logs as $log) {
            if ($previousStatus !== null && $previousStatus !== $log->status) {
                $changes[] = [
                    'from' => $previousStatus,
                    'to' => $log->status,
                    'changed_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'log_id' => $log->id,
                ];
            }
            $previousStatus = $log->status;
        }
        
        return response()->json([
            'success' => true,
            'data' => $changes,
            'total_changes' => count($changes),
        ]);
    }
    
    /**
     * Get latest status for each service.
     */
    public function getLatestStatuses()
    {
        $latestLogs = ServiceLog::with('service')
                               ->whereIn('id', function($query) {
                                   $query->select(DB::raw('MAX(id)'))
                                       ->from('service_logs')
                                       ->groupBy('service_id');
                               })
                               ->get();
        
        return response()->json([
            'success' => true,
            'data' => $latestLogs,
        ]);
    }
    
    /**
     * Export logs to CSV.
     */
    public function export(Request $request)
    {
        $query = ServiceLog::with('service');
        
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'service_logs_' . now()->format('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Service Name',
                'Service ID',
                'Status',
                'Response Time (s)',
                'Response Code',
                'Message',
                'Created At',
                'Checked At'
            ]);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->service->name ?? 'Unknown Service',
                    $log->service_id,
                    $log->status ?? 'UNKNOWN',
                    number_format($log->response_time ?? 0, 2),
                    $log->response_code ?? '-',
                    $log->message ?? '-',
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->checked_at ? $log->checked_at->format('Y-m-d H:i:s') : '-',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Delete old logs (bulk delete).
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:service_logs,id',
        ]);
        
        $deleted = ServiceLog::whereIn('id', $request->ids)->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} logs",
            'deleted_count' => $deleted,
        ]);
    }
    
    /**
     * Clear logs older than specified days.
     */
    public function clearOldLogs(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);
        
        $cutoffDate = now()->subDays($request->days);
        $deleted = ServiceLog::where('created_at', '<', $cutoffDate)->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} logs older than {$request->days} days",
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
        ]);
    }

    // ================================================================
    // 📡 API METHODS - UNTUK POSTMAN / MOBILE APP
    // ================================================================

    /**
     * ============================================================
     *  📡 API: GET ALL LOGS
     *  ============================================================
     *  🔗 URL: GET /api/logs
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=20&page=1&type=service
     * ============================================================
     */
    public function apiIndex(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $type = $request->input('type'); // service / smoke
            
            if ($type === 'smoke') {
                $logs = SmokeLog::orderBy('created_at', 'desc')
                    ->paginate($perPage);
            } else {
                $logs = ServiceLog::with('service')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);
            }
            
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
                'message' => 'Gagal mengambil logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE LOGS
     *  ============================================================
     *  🔗 URL: GET /api/logs/service
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=20&status=UP&date_from=2026-01-01&date_to=2026-01-31
     * ============================================================
     */
    public function apiServiceLogs(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $status = $request->input('status');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $serviceId = $request->input('service_id');

            $query = ServiceLog::with('service');

            if ($serviceId) {
                $query->where('service_id', $serviceId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

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
                'message' => 'Gagal mengambil service logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SMOKE LOGS
     *  ============================================================
     *  🔗 URL: GET /api/logs/smoke
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=20&date_from=2026-01-01&date_to=2026-01-31
     * ============================================================
     */
    public function apiSmokeLogs(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $status = $request->input('status'); // NORMAL, WARNING, DANGER

            $query = SmokeLog::orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            $logs = $query->paginate($perPage);

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
                'message' => 'Gagal mengambil smoke logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SERVICE LOGS BY ID
     *  ============================================================
     *  🔗 URL: GET /api/logs/service/{id}
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=20&status=UP
     * ============================================================
     */
    public function apiServiceLogsById($id, Request $request)
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

            $logs = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'target' => $service->target,
                        'type' => $service->type,
                        'last_status' => $service->last_status,
                    ],
                    'logs' => $logs->items(),
                    'pagination' => [
                        'total' => $logs->total(),
                        'per_page' => $logs->perPage(),
                        'current_page' => $logs->currentPage(),
                        'last_page' => $logs->lastPage(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil logs: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET LOG STATISTICS
     *  ============================================================
     *  🔗 URL: GET /api/logs/stats
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function apiStats()
    {
        try {
            $stats = [
                'total' => ServiceLog::count(),
                'up' => ServiceLog::where('status', 'UP')->count(),
                'warning' => ServiceLog::where('status', 'WARNING')->count(),
                'down' => ServiceLog::where('status', 'DOWN')->count(),
                'unknown' => ServiceLog::where('status', 'UNKNOWN')->count(),
                'today' => ServiceLog::whereDate('created_at', today())->count(),
                'this_week' => ServiceLog::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'smoke_total' => SmokeLog::count(),
                'smoke_today' => SmokeLog::whereDate('created_at', today())->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik logs: ' . $e->getMessage()
            ], 500);
        }
    }
}