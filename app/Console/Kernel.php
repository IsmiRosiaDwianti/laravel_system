<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\CheckSmokeDevices::class,
        \App\Console\Commands\CheckServices::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // 🚀 SEMUA SCHEDULE SUDAH DI ROUTES/CONSOLE.PHP
        // ============================================================
        // KOSONGKAN!
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}