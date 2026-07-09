<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EspMonitorService;
use Illuminate\Support\Facades\Log;

class CheckSmokeDevices extends Command
{
    protected $signature = 'app:check-smoke-devices';
    protected $description = 'Check smoke devices (ESP) status and send WhatsApp alerts';

    public function handle()
    {
        // 🔥 LOG KE FILE UNTUK MONITORING
        Log::info('🚀 app:check-smoke-devices dijalankan - ' . now()->format('Y-m-d H:i:s'));
        
        $this->info('🔍 Memulai monitoring smoke devices...');
        $this->line('📡 Waktu: ' . now()->format('Y-m-d H:i:s'));

        try {
            $espMonitor = new EspMonitorService();
            $espMonitor->checkEspStatus();

            $this->info('✅ Monitoring smoke devices selesai');
            Log::info('✅ app:check-smoke-devices selesai - ' . now()->format('Y-m-d H:i:s'));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('❌ app:check-smoke-devices error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}