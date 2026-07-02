<?php

namespace App\Http\Controllers;

use App\Models\ServiceLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * Display a listing of the logs.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Query dasar dengan relasi service
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
        
        // Filter berdasarkan perubahan status
        if ($request->has('status_changed') && $request->status_changed == '1') {
            $query->whereIn('id', function($subquery) {
                $subquery->select(DB::raw('MAX(id)'))
                    ->from('service_logs as sl1')
                    ->whereExists(function($exists) {
                        $exists->select(DB::raw(1))
                            ->from('service_logs as sl2')
                            ->whereColumn('sl2.service_id', 'sl1.service_id')
                            ->whereColumn('sl2.created_at', '<', 'sl1.created_at')
                            ->whereRaw('sl2.status != sl1.status');
                    });
            });
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
        
        // Urutkan dan paginate - DEFAULT 10
        $logs = $query->latest()
                     ->paginate($request->per_page ?? 10)
                     ->withQueryString();
        
        // Ambil semua service untuk filter
        $services = Service::orderBy('name')->get();
        
        // Statistik tambahan
        $stats = $this->getStats();
        
        // Hitung jumlah log yang statusnya berubah
        $statusChangedCount = $this->getStatusChangedCount();
        
        return view('logs', compact(
            'logs',
            'services',
            'stats',
            'statusChangedCount'
        ));
    }
    
    /**
     * Get statistics for logs.
     *
     * @return array
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
     *
     * @return int
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
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $log = ServiceLog::with('service')->findOrFail($id);
        
        // Get previous and next log for navigation
        $previousLog = ServiceLog::where('service_id', $log->service_id)
                               ->where('id', '<', $id)
                               ->latest()
                               ->first();
        
        $nextLog = ServiceLog::where('service_id', $log->service_id)
                           ->where('id', '>', $id)
                           ->oldest()
                           ->first();
        
        // Get status history for this service
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
     *
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
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
            
            // Add CSV headers
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
            
            // Add data rows
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
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
}