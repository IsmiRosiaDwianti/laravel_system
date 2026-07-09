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
    // ==================== TIDAK ADA CONSTRUCTOR ====================
    // Middleware auth sudah di route web.php

    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        // ==================== STATISTIK SERVICE ====================
        $total = Service::count();
        $up = Service::where('last_status', 'UP')->count();
        $warning = Service::where('last_status', 'WARNING')->count();
        $down = Service::where('last_status', 'DOWN')->count();

        // ==================== DATA SERVICE ====================
        $services = Service::orderBy('id', 'desc')->get();
        $latestServices = Service::orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        // ==================== GRAFIK UPTIME 7 HARI (DENGAN BOBOT) ====================
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $logs = ServiceLog::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        // Kelompokkan berdasarkan tanggal
        $groupedLogs = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $chartLabels = [];
        $uptimeData = [];

        // Buat range 7 hari terakhir
        $current = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $chartLabels[] = $current->format('d/m/Y');
            
            if (isset($groupedLogs[$key])) {
                $dayLogs = $groupedLogs[$key];
                $totalChecks = $dayLogs->count();
                
                // HITUNG DENGAN BOBOT (SAMA SEPERTI DJANGO)
                // UP = 100, WARNING = 70, DOWN = 0
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
                
                // Rata-rata bobot = persentase hari itu
                $uptimeData[] = $totalChecks > 0 
                    ? round($totalWeight / $totalChecks, 2) 
                    : 0;
            } else {
                $uptimeData[] = 0;
            }
            
            $current->addDay();
        }

        // ==================== UPTIME RATE KESELURUHAN (DENGAN BOBOT) ====================
        // Ambil semua log yang ada (bukan hanya 7 hari)
        $allLogs = ServiceLog::all();
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

        // ==================== GRAFIK SMOKE (7 HARI - PER HARI) ====================
        $smokeStartDate = Carbon::now()->subDays(6)->startOfDay();
        $smokeEndDate = Carbon::now()->endOfDay();
        
        $smokeLogs = SmokeLog::where('created_at', '>=', $smokeStartDate)
            ->where('created_at', '<=', $smokeEndDate)
            ->orderBy('created_at', 'asc')
            ->get();

        // Kelompokkan berdasarkan tanggal
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
        // Ambil semua smoke device
        $smokeDevices = SmokeDevice::all();
        
        // Cek apakah ada device yang online (kirim data dalam 2 menit terakhir)
        $onlineCount = 0;
        $lastSmokeValue = 0;
        $lastSmokeStatus = 'NORMAL';
        $lastSeenAt = null;
        $deviceName = 'ESP32-Smoke';
        
        foreach ($smokeDevices as $device) {
            if ($device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2) {
                $onlineCount++;
            }
            
            // Ambil data terakhir
            $lastSmokeValue = $device->smoke_value ?? 0;
            $lastSmokeStatus = $device->status ?? 'NORMAL';
            $lastSeenAt = $device->last_seen_at;
            $deviceName = $device->name ?? 'ESP32-Smoke';
        }

        // Tentukan status ESP untuk dashboard
        $espStatus = $onlineCount > 0 ? 'ONLINE' : 'OFFLINE';
        $espStatusClass = $onlineCount > 0 ? 'online' : 'offline';
        $espStatusLabel = $onlineCount > 0 ? '🟢 ONLINE' : '🔴 OFFLINE';

        // ==================== KIRIM KE VIEW ====================
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
                'uptimeData',          // Data grafik 7 hari (bobot)
                'uptimeOverall',       // Uptime keseluruhan (bobot)
                'smokeLabels',
                'smokeData',
                'onlineCount',
                'espStatus',
                'espStatusClass',
                'espStatusLabel',
                'lastSmokeValue',
                'lastSmokeStatus',
                'lastSeenAt',
                'deviceName'
            )
        );
    }
}