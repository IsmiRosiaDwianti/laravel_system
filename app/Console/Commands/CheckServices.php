<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\ServiceLog;
use Illuminate\Support\Facades\Http;

class CheckServices extends Command
{
    protected $signature = 'monitor:services';

    protected $description = 'Check all services';

    public function handle()
    {
        $services = Service::all();

        foreach ($services as $service) {

            if ($service->type != 'http') {
                continue;
            }

            try {

                $start = microtime(true);

                $response = Http::timeout(10)
                    ->get($service->target);

                $time = round(
                    microtime(true) - $start,
                    2
                );

                $code = $response->status();

                if ($code == 200) {

                    $status = 'UP';

                    $message = 'Service normal';

                } else {

                    $status = 'WARNING';

                    $message = 'HTTP Code '.$code;

                }

            } catch (\Exception $e) {

                $status = 'DOWN';

                $code = 'N/A';

                $time = 0;

                $message = $e->getMessage();
            }

            $service->update([
                'last_status' => $status,
                'last_code' => $code,
                'last_response_time' => $time,
                'last_message' => $message
            ]);

            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $status,
                'response_code' => $code,
                'response_time' => $time,
                'message' => $message
            ]);

            $this->info(
                $service->name .
                ' => ' .
                $status
            );
        }
    }
}