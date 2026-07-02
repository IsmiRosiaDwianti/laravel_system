<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Services\ServiceMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorServices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:services';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor all services and update their status automatically';

    /**
     * Execute the console command.
     */
    public function handle(ServiceMonitorService $monitor)
    {
        $this->info('🔄 Starting service monitoring...');
        $startTime = microtime(true);

        $services = Service::all();
        $total = $services->count();
        $success = 0;
        $failed = 0;
        $statusChanges = [];

        if ($total === 0) {
            $this->warn('⚠️ No services found to monitor.');
            return Command::SUCCESS;
        }

        $this->output->progressStart($total);

        foreach ($services as $service) {
            try {
                // Simpan status lama sebelum di-check
                $oldStatus = $service->last_status;
                
                // Jalankan monitoring
                $monitor->check($service);
                
                // Refresh service untuk mendapatkan status terbaru
                $service->refresh();
                
                // Catat jika ada perubahan status
                if ($oldStatus !== $service->last_status) {
                    $statusChanges[] = [
                        'service' => $service->name,
                        'old' => $oldStatus ?? 'UNKNOWN',
                        'new' => $service->last_status
                    ];
                }
                
                $success++;
                $this->output->progressAdvance();
                
            } catch (\Exception $e) {
                $failed++;
                Log::error('❌ Monitoring error for service ID ' . $service->id . ': ' . $e->getMessage(), [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'target' => $service->target
                ]);
                $this->output->progressAdvance();
            }
        }

        $this->output->progressFinish();

        $executionTime = round(microtime(true) - $startTime, 2);
        
        // Tampilkan summary
        $this->newLine();
        $this->info('✅ Monitoring completed!');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("📊 Total Services  : {$total}");
        $this->line("✅ Success         : {$success}");
        $this->line("❌ Failed          : {$failed}");
        $this->line("⏱️ Execution Time  : {$executionTime} seconds");
        
        // Tampilkan perubahan status
        if (!empty($statusChanges)) {
            $this->newLine();
            $this->warn('⚠️ Status Changes Detected:');
            $this->table(
                ['Service', 'Old Status', 'New Status'],
                array_map(function($change) {
                    return [
                        $change['service'],
                        $this->getStatusEmoji($change['old']) . ' ' . $change['old'],
                        $this->getStatusEmoji($change['new']) . ' ' . $change['new']
                    ];
                }, $statusChanges)
            );
        } else {
            $this->info('✅ No status changes detected.');
        }

        // Log ke file
        Log::info('Service monitoring completed', [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'execution_time' => $executionTime,
            'status_changes' => $statusChanges
        ]);

        return Command::SUCCESS;
    }

    /**
     * Get emoji for status
     */
    private function getStatusEmoji($status)
    {
        return match($status) {
            'UP' => '🟢',
            'WARNING' => '🟠',
            'DOWN' => '🔴',
            default => '⚪'
        };
    }
}