<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Services\ServiceMonitorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // ✅ TAMBAHKAN INI

class CheckServices extends Command
{
    protected $signature = 'monitor:services';
    protected $description = 'Monitoring all services';

    public function handle(ServiceMonitorService $monitor)
    {
        // ============================================================
        // 🔥 CEK INTERNET - JIKA MATI, LANGSUNG KELUAR
        // ============================================================
        if (!$monitor->checkNetworkConnection()) {
            Cache::put('internet_down', true, 300); // ✅ TAMBAHKAN CACHE
            Log::info('⏸️ monitor:services SKIPPED - Internet DOWN');
            $this->warn('⏸️ Internet DOWN - Monitoring skipped');
            return Command::SUCCESS; // 🔥 LANGSUNG KELUAR
        }

        // ✅ INTERNET NORMAL
        Cache::forget('internet_down'); // ✅ HAPUS FLAG
        Cache::put('internet_available', true, 60); // ✅ SET FLAG ONLINE

        // ============================================================
        // 🔥 KODE LAMA (TIDAK DIUBAH)
        // ============================================================
        $this->info('🔍 Memulai monitoring services...');
        Log::info('🌐 Internet tersedia, memulai monitor:services');

        $services = Service::all();

        if ($services->isEmpty()) {
            $this->warn('⚠️ Tidak ada service yang terdaftar');
            return Command::SUCCESS;
        }

        $this->info('📡 Total service: ' . $services->count());

        foreach ($services as $service) {
            $monitor->check($service);
            $this->line("✅ {$service->name} checked");
        }

        $this->info('✅ Monitoring services selesai');
        Log::info('✅ monitor:services selesai');
        
        return Command::SUCCESS;
    }
}