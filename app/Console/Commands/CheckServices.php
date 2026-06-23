<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Http;

class CheckServices extends Command
{
    protected $signature = 'monitor:services';

    protected $description = 'Check all services';

    private function sendWhatsappNotification(
        $service,
        $status,
        $code,
        $time,
        $message
    ) {

        $contacts = Contact::where(
            'is_active',
            true
        )->get();

        if ($contacts->count() == 0) {
            return;
        }

        if ($status == 'DOWN') {

            $waMessage =
"🔴 SERVICE DOWN

📌 Nama Service : {$service->name}
🔗 Link URL : {$service->target}

⚠️ Status : DOWN
📟 Code : {$code}
⏱️ Time : {$time}s

🔧 TINDAKAN:
📡 Host tidak merespon atau service tidak dapat diakses

🕐 " . now()->format('d-m-Y H:i:s') . "

> Sent via fonnte.com";

        } elseif ($status == 'WARNING') {

            $waMessage =
"🟠 SERVICE WARNING

📌 Nama Service : {$service->name}
🔗 Link URL : {$service->target}

⚠️ Status : WARNING
📟 Code : {$code}
⏱️ Time : {$time}s

💡 Masalah :
{$message}

🔧 TINDAKAN:
⏱️ Periksa performa server atau aplikasi

🕐 " . now()->format('d-m-Y H:i:s') . "

> Sent via fonnte.com";

        } else {

            $waMessage =
"🟢 SERVICE NORMAL

📌 Nama Service : {$service->name}
🔗 Link URL : {$service->target}

✅ Status : UP
📟 Code : {$code}
⏱️ Time : {$time}s

🕐 " . now()->format('d-m-Y H:i:s') . "

> Sent via fonnte.com";
        }

        foreach ($contacts as $contact) {

            try {

                FonnteService::send(
                    $contact->phone,
                    $waMessage
                );

            } catch (\Exception $e) {

                $this->error(
                    'WA gagal dikirim ke ' .
                    $contact->phone
                );
            }
        }
    }

    public function handle()
    {
        $services = Service::all();

        foreach ($services as $service) {

            $oldStatus = $service->last_status;

            /*
            |--------------------------------------------------------------------------
            | HTTP CHECK
            |--------------------------------------------------------------------------
            */

            if ($service->type == 'http') {

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

                        $message =
                            'HTTP Code ' . $code;
                    }

                } catch (\Exception $e) {

                    $status = 'DOWN';

                    $code = 'N/A';

                    $time = 0;

                    $message =
                        $e->getMessage();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PING CHECK
            |--------------------------------------------------------------------------
            */

            elseif ($service->type == 'ping') {

                $host = escapeshellarg(
                    $service->target
                );

                $output = [];

                $result = null;

                exec(
                    "ping -n 1 $host",
                    $output,
                    $result
                );

                if ($result == 0) {

                    $status = 'UP';

                    $code = 'PING';

                    $time = 1;

                    $message =
                        'Host merespon ping';

                } else {

                    $status = 'DOWN';

                    $code = 'N/A';

                    $time = 0;

                    $message =
                        'Host tidak merespon ping';
                }
            }

            else {

                continue;
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

            /*
            |--------------------------------------------------------------------------
            | ANTI SPAM NOTIFICATION
            |--------------------------------------------------------------------------
            */

            if ($oldStatus != $status) {

                $this->sendWhatsappNotification(
                    $service,
                    $status,
                    $code,
                    $time,
                    $message
                );
            }

            $this->info(
                $service->name .
                ' => ' .
                $status
            );
        }

        return Command::SUCCESS;
    }
}