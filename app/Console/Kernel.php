<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ==================== SERVICE MONITOR ====================
        $schedule->command('monitor:services')->everyFiveMinutes();
        
        // ==================== SMOKE/ESP MONITOR ====================
        $schedule->command('app:check-smoke-devices')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}