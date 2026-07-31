<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\SmokeLog;
use App\Models\SmokeDevice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        // ==================== STATISTIK SERVICE ====================
        // 🔥 HANYA SERVICE YANG AKTIF (TIDAK DIARSIPKAN)
        $total = Service::active()->count();
        $up = Service::active()->where('last_status', 'UP')->count();
        $warning = Service::active()->where('last_status', 'WARNING')->count();
        $down = Service::active()->where('last_status', 'DOWN')->count();

        // ==================== DATA SERVICE ====================
        // 🔥 HANYA SERVICE AKTIF
        $services = Service::active()->orderBy('id', 'desc')->get();
        $latestServices = Service::active()->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        // ==================== GRAFIK UPTIME 7 HARI ====================
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // 🔥 AMBIL ID SERVICE YANG AKTIF
        $activeServiceIds = Service::active()->pluck('id')->toArray();
        
        // 🔥 HANYA LOG DARI SERVICE AKTIF
        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedLogs = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $chartLabels = [];
        $uptimeData = [];

        $current = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $chartLabels[] = $current->format('d/m/Y');
            
            if (isset($groupedLogs[$key])) {
                $dayLogs = $groupedLogs[$key];
                $totalChecks = $dayLogs->count();
                
                $totalWeight = 0;
                foreach ($dayLogs as $log) {
                    if ($log->status === 'UP') {
                        $totalWeight += 100;
                    } elseif ($log->status === 'WARNING') {
                        $totalWeight += 70;
                    } elseif ($log->status === 'DOWN') {
                        $totalWeight += 0;
                    }
                }
                
                $uptimeData[] = $totalChecks > 0 
                    ? round($totalWeight / $totalChecks, 2) 
                    : 0;
            } else {
                $uptimeData[] = 0;
            }
            
            $current->addDay();
        }

        // ==================== DONUT CHART - STATUS 7 HARI ====================
        // 🔥 HITUNG TOTAL UP, WARNING, DOWN DALAM 7 HARI TERAKHIR
        $donutUp = 0;
        $donutWarning = 0;
        $donutDown = 0;

        // Ambil semua log 7 hari terakhir
        $sevenDayLogs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        foreach ($sevenDayLogs as $log) {
            if ($log->status === 'UP') {
                $donutUp++;
            } elseif ($log->status === 'WARNING') {
                $donutWarning++;
            } elseif ($log->status === 'DOWN') {
                $donutDown++;
            }
        }

        // ==================== UPTIME RATE KESELURUHAN ====================
        // 🔥 HANYA LOG DARI SERVICE AKTIF
        $allLogs = ServiceLog::whereIn('service_id', $activeServiceIds)->get();
        $totalAllLogs = $allLogs->count();
        
        if ($totalAllLogs > 0) {
            $totalWeightAll = 0;
            foreach ($allLogs as $log) {
                if ($log->status === 'UP') {
                    $totalWeightAll += 100;
                } elseif ($log->status === 'WARNING') {
                    $totalWeightAll += 70;
                } elseif ($log->status === 'DOWN') {
                    $totalWeightAll += 0;
                }
            }
            $uptimeOverall = round($totalWeightAll / $totalAllLogs, 2);
        } else {
            $uptimeOverall = 0;
        }

        // ==================== GRAFIK SMOKE (7 HARI) ====================
        $smokeStartDate = Carbon::now()->subDays(6)->startOfDay();
        $smokeEndDate = Carbon::now()->endOfDay();
        
        $smokeLogs = SmokeLog::where('created_at', '>=', $smokeStartDate)
            ->where('created_at', '<=', $smokeEndDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedSmokeLogs = $smokeLogs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $smokeLabels = [];
        $smokeData = [];

        $currentSmoke = Carbon::now()->subDays(6)->startOfDay();
        $endSmoke = Carbon::now()->endOfDay();

        while ($currentSmoke <= $endSmoke) {
            $key = $currentSmoke->format('Y-m-d');
            $smokeLabels[] = $currentSmoke->format('d/m/Y');
            
            if (isset($groupedSmokeLogs[$key])) {
                $avgSmoke = $groupedSmokeLogs[$key]->avg('smoke_value') ?? 0;
                $smokeData[] = round($avgSmoke, 2);
            } else {
                $smokeData[] = 0;
            }
            
            $currentSmoke->addDay();
        }

        // ==================== ESP STATUS ====================
        $smokeDevices = SmokeDevice::all();
        
        $onlineCount = 0;
        $lastSmokeValue = 0;
        $lastSmokeStatus = 'NORMAL';
        $lastSeenAt = null;
        $deviceName = 'ESP32-Smoke';
        
        foreach ($smokeDevices as $device) {
            if ($device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2) {
                $onlineCount++;
            }
            
            $lastSmokeValue = $device->smoke_value ?? 0;
            $lastSmokeStatus = $device->status ?? 'NORMAL';
            $lastSeenAt = $device->last_seen_at;
            $deviceName = $device->name ?? 'ESP32-Smoke';
        }

        $espStatus = $onlineCount > 0 ? 'ONLINE' : 'OFFLINE';
        $espStatusClass = $onlineCount > 0 ? 'online' : 'offline';
        $espStatusLabel = $onlineCount > 0 ? '🟢 ONLINE' : '🔴 OFFLINE';

        return view(
            'dashboard',
            compact(
                'total',
                'up',
                'warning',
                'down',
                'services',
                'latestServices',
                'chartLabels',
                'uptimeData',
                'uptimeOverall',
                'smokeLabels',
                'smokeData',
                'onlineCount',
                'espStatus',
                'espStatusClass',
                'espStatusLabel',
                'lastSmokeValue',
                'lastSmokeStatus',
                'lastSeenAt',
                'deviceName',
                // 🔥 DATA BARU UNTUK DONUT CHART
                'donutUp',
                'donutWarning',
                'donutDown'
            )
        );
    }

    /**
     * ============================================================
     * 🔥 GET DASHBOARD DATA FOR AJAX POLLING (TANPA RELOAD HALAMAN)
     * ============================================================
     * 🔗 URL: GET /dashboard/data
     * 🔑 Butuh Auth: Session (web)
     * 📦 Response: JSON dengan data dashboard terbaru
     * ============================================================
     */
    public function getDashboardData()
    {
        // 🔥 HANYA SERVICE AKTIF (TIDAK DIARSIPKAN)
        $activeServices = Service::active()->get();
        $activeServiceIds = Service::active()->pluck('id')->toArray();
        
        $total = $activeServices->count();
        $up = $activeServices->where('last_status', 'UP')->count();
        $warning = $activeServices->where('last_status', 'WARNING')->count();
        $down = $activeServices->where('last_status', 'DOWN')->count();
        
        // Uptime
        $uptime = $total > 0 ? round(($up / $total) * 100, 2) : 0;
        
        // ESP Status
        $smokeDevices = SmokeDevice::all();
        $onlineCount = 0;
        $lastSmokeValue = 0;
        $lastSmokeStatus = 'NORMAL';
        $lastSeenAt = null;
        
        foreach ($smokeDevices as $device) {
            if ($device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2) {
                $onlineCount++;
            }
            $lastSmokeValue = $device->smoke_value ?? 0;
            $lastSmokeStatus = $device->status ?? 'NORMAL';
            $lastSeenAt = $device->last_seen_at;
        }
        
        // ==================== DONUT CHART - STATUS 7 HARI ====================
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $donutUp = 0;
        $donutWarning = 0;
        $donutDown = 0;

        $sevenDayLogs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        foreach ($sevenDayLogs as $log) {
            if ($log->status === 'UP') {
                $donutUp++;
            } elseif ($log->status === 'WARNING') {
                $donutWarning++;
            } elseif ($log->status === 'DOWN') {
                $donutDown++;
            }
        }
        
        // ==================== UPTIME CHART 7 HARI ====================
        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $groupedLogs = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });
        
        $chartLabels = [];
        $uptimeData = [];
        
        $current = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();
        
        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $chartLabels[] = $current->format('d/m/Y');
            
            if (isset($groupedLogs[$key])) {
                $dayLogs = $groupedLogs[$key];
                $totalChecks = $dayLogs->count();
                $totalWeight = 0;
                foreach ($dayLogs as $log) {
                    if ($log->status === 'UP') $totalWeight += 100;
                    elseif ($log->status === 'WARNING') $totalWeight += 70;
                    elseif ($log->status === 'DOWN') $totalWeight += 0;
                }
                $uptimeData[] = $totalChecks > 0 ? round($totalWeight / $totalChecks, 2) : 0;
            } else {
                $uptimeData[] = 0;
            }
            $current->addDay();
        }
        
        // ==================== SMOKE CHART 7 HARI ====================
        $smokeLogs = SmokeLog::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $groupedSmokeLogs = $smokeLogs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });
        
        $smokeLabels = [];
        $smokeData = [];
        
        $currentSmoke = Carbon::now()->subDays(6)->startOfDay();
        $endSmoke = Carbon::now()->endOfDay();
        
        while ($currentSmoke <= $endSmoke) {
            $key = $currentSmoke->format('Y-m-d');
            $smokeLabels[] = $currentSmoke->format('d/m/Y');
            if (isset($groupedSmokeLogs[$key])) {
                $smokeData[] = round($groupedSmokeLogs[$key]->avg('smoke_value') ?? 0, 2);
            } else {
                $smokeData[] = 0;
            }
            $currentSmoke->addDay();
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total' => $total,
                    'up' => $up,
                    'warning' => $warning,
                    'down' => $down,
                    'uptime' => $uptime,
                ],
                'esp' => [
                    'online' => $onlineCount > 0,
                    'status' => $onlineCount > 0 ? 'ONLINE' : 'OFFLINE',
                    'smoke_value' => $lastSmokeValue,
                    'smoke_status' => $lastSmokeStatus,
                    'last_seen_human' => $lastSeenAt ? Carbon::parse($lastSeenAt)->diffForHumans() : null,
                ],
                'charts' => [
                    // 🔥 DATA DONUT CHART
                    'donut' => [
                        'up' => $donutUp,
                        'warning' => $donutWarning,
                        'down' => $donutDown,
                    ],
                    'uptime_labels' => $chartLabels,
                    'uptime_data' => $uptimeData,
                    'smoke_labels' => $smokeLabels,
                    'smoke_data' => $smokeData,
                ]
            ]
        ]);
    }
}