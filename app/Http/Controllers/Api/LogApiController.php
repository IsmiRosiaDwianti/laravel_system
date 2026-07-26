<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceLog;
use App\Models\SmokeLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogApiController extends Controller
{
    /**
     * ============================================================
     *  📡 API: GET ALL LOGS
     *  ============================================================
     *  🔗 URL: GET /api/logs
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?per_page=20&page=1&type=service
     * ============================================================
     */
    public function index(Request $request)
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
    public function serviceLogs(Request $request)
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
    public function smokeLogs(Request $request)
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
    public function serviceLogsById($id, Request $request)
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
    public function stats()
    {
        try {
            $stats = [
                'service' => [
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
                ],
                'smoke' => [
                    'total' => SmokeLog::count(),
                    'today' => SmokeLog::whereDate('created_at', today())->count(),
                    'this_week' => SmokeLog::whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])->count(),
                ]
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

    /**
     * ============================================================
     *  📡 API: GET STATUS HISTORY
     *  ============================================================
     *  🔗 URL: GET /api/logs/status-history/{serviceId}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function statusHistory($serviceId)
    {
        try {
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET LATEST STATUSES
     *  ============================================================
     *  🔗 URL: GET /api/logs/latest-statuses
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function latestStatuses()
    {
        try {
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
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil latest statuses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET LOG DETAIL
     *  ============================================================
     *  🔗 URL: GET /api/logs/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function show($id)
    {
        try {
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
            
            return response()->json([
                'success' => true,
                'data' => [
                    'log' => $log,
                    'previous_log' => $previousLog,
                    'next_log' => $nextLog,
                    'status_history' => $statusHistory,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Log tidak ditemukan'
            ], 404);
        }
    }

    /**
     * ============================================================
     *  📡 API: BULK DELETE LOGS
     *  ============================================================
     *  🔗 URL: DELETE /api/logs/bulk-delete
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "ids": [1, 2, 3] }
     * ============================================================
     */
    public function bulkDelete(Request $request)
    {
        try {
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: CLEAR OLD LOGS
     *  ============================================================
     *  🔗 URL: DELETE /api/logs/clear-old
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "days": 30 }
     * ============================================================
     */
    public function clearOldLogs(Request $request)
    {
        try {
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API: GET SMOKE LOG DETAIL
     *  ============================================================
     *  🔗 URL: GET /api/logs/smoke/{id}
     *  🔑 Butuh Auth: Sanctum Token
     * ============================================================
     */
    public function showSmokeLog($id)
    {
        try {
            $log = SmokeLog::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $log
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Smoke log tidak ditemukan'
            ], 404);
        }
    }
}