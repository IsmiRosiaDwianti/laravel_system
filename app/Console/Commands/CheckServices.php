<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Services\ServiceMonitorService;

class CheckServices extends Command
{
    protected $signature =
        'monitor:services';

    protected $description =
        'Monitoring all services';

    public function handle(
        ServiceMonitorService $monitor
    )
    {
        $services = Service::all();

        foreach ($services as $service) {

            $monitor->check($service);

            $this->info(
                $service->name .
                ' checked'
            );
        }
    }
}