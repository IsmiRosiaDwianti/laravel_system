<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Services\ServiceMonitorService;

class CheckServices extends Command
{
    protected $signature = 'monitor:services';
    protected $description = 'Monitoring all services';

    public function handle(ServiceMonitorService $monitor)
    {
        $this->info('🔍 Memulai monitoring services...');

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
        return Command::SUCCESS;
    }
}