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
        
        // 🔥 FILTER: HANYA SERVICE YANG AKTIF (TIDAK DIARSIPKAN)
        $query->whereHas('service', function($q) {
            $q->where('is_archived', false);
        });
        
        // ============================================================
        // 🔥 FILTER TAMPILAN LOG - 2 PILIHAN SAJA!
        // ============================================================
        // 1. all_logs=1   = 📋 Semua Perubahan (Status ATAU Code berubah)
        // 2. (default)    = 🔄 Hanya Perubahan Status
        // ============================================================
        
        if ($request->has('all_logs') && $request->all_logs) {
            // 📋 SEMUA PERUBAHAN (Status + Code)
            // Tampilkan log yang: status berubah ATAU response code berubah
            $query->where(function($q) {
                $q->where('is_status_change', true)
                  ->orWhereRaw('response_code != (SELECT last_code FROM services WHERE services.id = service_logs.service_id)');
            });
        } else {
            // 🔄 HANYA PERUBAHAN STATUS
            $query->where('is_status_change', true);
        }
        
        // ============================================================
        // 🔥 FILTER LAINNYA
        // ============================================================
        
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
                  ->orWhere('previous_status', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function($subq) use ($search) {
                      $subq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // ============================================================
        // 🔥 STATISTIK (SEBELUM PAGINATE)
        // ============================================================
        $statsQuery = clone $query;
        $stats = [
            'total' => $statsQuery->count(),
            'up' => (clone $statsQuery)->where('status', 'UP')->count(),
            'warning' => (clone $statsQuery)->where('status', 'WARNING')->count(),
            'down' => (clone $statsQuery)->where('status', 'DOWN')->count(),
            'unknown' => (clone $statsQuery)->where('status', 'UNKNOWN')->count(),
        ];
        
        // 🔥 STATISTIK PERUBAHAN STATUS (GLOBAL)
        $totalChanges = ServiceLog::whereHas('service', function($q) {
            $q->where('is_archived', false);
        })->where('is_status_change', true)->count();
        
        // 🔥 STATISTIK PERUBAHAN CODE (GLOBAL)
        $totalCodeChanges = ServiceLog::whereHas('service', function($q) {
            $q->where('is_archived', false);
        })
        ->whereRaw('response_code != (SELECT last_code FROM services WHERE services.id = service_logs.service_id)')
        ->count();
        
        // ============================================================
        // 🔥 PAGINATION
        // ============================================================
        $perPage = $request->input('perPage', $request->input('per_page', 10));
        $perPage = (int) $perPage;
        
        if ($perPage < 1) {
            $perPage = 10;
        }
        
        if ($perPage > 100) {
            $perPage = 100;
        }
        
        $logs = $query->latest('created_at')
                     ->paginate($perPage)
                     ->withQueryString();
        
        // ==================== AMBIL SERVICE UNTUK FILTER ====================
        $services = Service::active()->orderBy('name')->get();
        
        // ==================== KIRIM KE VIEW ====================
        return view('logs', compact(
            'logs', 
            'stats', 
            'services', 
            'perPage',
            'totalChanges',
            'totalCodeChanges'
        ));
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
        return ServiceLog::where('is_status_change', true)->count();
    }
    
    /**
     * Show specific log details.
     */
    public function show($id)
    {
        $log = ServiceLog::with('service')->findOrFail($id);
        
        // 🔥 CEK APAKAH SERVICE DIARSIPKAN
        if ($log->service && $log->service->is_archived) {
            return redirect()
                ->route('logs')
                ->with('error', 'Log ini berasal dari service yang diarsipkan. Pulihkan service terlebih dahulu.');
        }
        
        $previousLog = ServiceLog::where('service_id', $log->service_id)
                               ->where('id', '<', $id)
                               ->latest()
                               ->first();
        
        $nextLog = ServiceLog::where('service_id', $log->service_id)
                           ->where('id', '>', $id)
                           ->oldest()
                           ->first();
        
        $statusHistory = ServiceLog::where('service_id', $log->service_id)
                                 ->select('status', 'response_code', 'created_at', 'id')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(20)
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
        $service = Service::find($serviceId);
        
        // 🔥 CEK APAKAH SERVICE DIARSIPKAN
        if ($service && $service->is_archived) {
            return response()->json([
                'success' => false,
                'message' => 'Service sedang diarsipkan',
                'data' => [],
                'total_changes' => 0,
            ], 403);
        }
        
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
                    'response_code' => $log->response_code,
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
                               ->whereHas('service', function($q) {
                                   $q->where('is_archived', false);
                               })
                               ->whereIn('id', function($query) {
                                   $query->select(DB::raw('MAX(id)'))
                                       ->from('service_logs')
                                       ->whereHas('service', function($q) {
                                           $q->where('is_archived', false);
                                       })
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
        $query = ServiceLog::with('service')
                           ->whereHas('service', function($q) {
                               $q->where('is_archived', false);
                           });
        
        // 🔥 TERAPKAN FILTER YANG SAMA
        if ($request->has('all_logs') && $request->all_logs) {
            $query->where(function($q) {
                $q->where('is_status_change', true)
                  ->orWhereRaw('response_code != (SELECT last_code FROM services WHERE services.id = service_logs.service_id)');
            });
        } else {
            $query->where('is_status_change', true);
        }
        
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
            
            // 🔥 HEADER CSV
            fputcsv($file, [
                'ID',
                'Service Name',
                'Service ID',
                'Status',
                'Previous Status',
                'Response Time (s)',
                'Response Code',
                'Status Change',
                'Message',
                'Action',
                'Created At',
                'Checked At',
            ]);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->service->name ?? 'Unknown Service',
                    $log->service_id,
                    $log->status ?? 'UNKNOWN',
                    $log->previous_status ?? '-',
                    number_format($log->response_time ?? 0, 2),
                    $log->response_code ?? '-',
                    $log->is_status_change ? 'YES' : 'NO',
                    $log->message ?? '-',
                    $log->action ?? '-',
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
        
        // 🔥 CEK APAKAH LOG DARI SERVICE AKTIF
        $logs = ServiceLog::whereIn('id', $request->ids)
                          ->whereHas('service', function($q) {
                              $q->where('is_archived', false);
                          })
                          ->get();
        
        if ($logs->count() !== count($request->ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa log berasal dari service yang diarsipkan atau tidak ditemukan',
            ], 422);
        }
        
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
        
        // 🔥 HAPUS LOG LAMA DARI SERVICE AKTIF
        $deleted = ServiceLog::whereHas('service', function($q) {
            $q->where('is_archived', false);
        })
        ->where('created_at', '<', $cutoffDate)
        // 🔥 JANGAN HAPUS LOG PERUBAHAN STATUS (penting untuk histori)
        ->where('is_status_change', false)
        ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} logs older than {$request->days} days (status change logs kept)",
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * 🔥 BARU: Get logs by response code (untuk dashboard/filter)
     */
    public function getByResponseCode($code)
    {
        $logs = ServiceLog::with('service')
                          ->whereHas('service', function($q) {
                              $q->where('is_archived', false);
                          })
                          ->where('response_code', $code)
                          ->latest('created_at')
                          ->limit(20)
                          ->get();
        
        return response()->json([
            'success' => true,
            'data' => $logs,
            'count' => $logs->count(),
        ]);
    }
}