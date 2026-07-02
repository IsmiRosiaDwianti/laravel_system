<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\EspMonitorService; // Tambahkan ini untuk ESP

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ==================== SERVICE MONITOR ====================
        // Monitor service (HTTP/Ping) setiap 5 menit
        $schedule->command('monitor:services')->everyFiveMinutes();
        
        // ==================== SMOKE/ESP MONITOR ====================
        // Monitor smoke devices (ESP) setiap menit
        $schedule->command('monitor:smoke-devices')->everyMinute();
        
        // ==================== ESP STATUS ALERT (WA) ====================
        // Cek ESP offline > 2 menit dan kirim WhatsApp alert
        $schedule->call(function () {
            $espMonitor = new EspMonitorService();
            $espMonitor->checkEspStatus();
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}