<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckSmokeDevices::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // ==================== SMOKE/ESP MONITOR ====================
        // 🔥 PAKAI CARA INI (LANGSUNG PAKAI CLASS)
        $schedule->command(\App\Console\Commands\CheckSmokeDevices::class)->everyMinute();
        
        // ==================== SERVICE MONITOR ====================
        $schedule->command('monitor:services')->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}