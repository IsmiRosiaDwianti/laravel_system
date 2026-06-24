<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use App\Services\FonnteService;

class ServiceMonitorService
{
    public function check(Service $service)
    {
        if ($service->type === 'ping') {

            return $this->checkPing($service);

        }

        return $this->checkHttp($service);
    }

    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status;

        try {

            $url = $service->target;

            if (
                !str_starts_with($url, 'http://')
                &&
                !str_starts_with($url, 'https://')
            ) {
                $url = 'https://' . $url;
            }

            $start = microtime(true);

            $response = Http::timeout(10)
                ->get($url);

            $time = round(
                microtime(true) - $start,
                2
            );

            $code = $response->status();

            $analysis = $this->analyzeResponse(
                $code,
                $time
            );

            $status = $analysis['status'];
            $reason = $analysis['reason'];
            $detail = $analysis['detail'];
            $action = $analysis['action'];

        } catch (\Exception $e) {

            $time = 0;

            $code = 'N/A';

            $analysis = $this->analyzeException(
                $e->getMessage()
            );

            $status = 'DOWN';
            $reason = $analysis['reason'];
            $detail = $analysis['detail'];
            $action = $analysis['action'];
        }

        $this->saveResult(
            $service,
            $oldStatus,
            $status,
            $code,
            $time,
            $reason,
            $detail,
            $action
        );
    }

    private function checkPing(Service $service)
    {
        $oldStatus = $service->last_status;

        $start = microtime(true);

        exec(
            "ping -n 1 " . escapeshellarg($service->target),
            $output,
            $result
        );

        $time = round(
            microtime(true) - $start,
            2
        );

        if ($result === 0) {

            $status = 'UP';

            $code = 'PING';

            $reason = 'PING_OK';

            $detail = 'Host merespon ping';

            $action = '-';

        } else {

            $status = 'DOWN';

            $code = 'N/A';

            $reason = 'PING_FAILED';

            $detail = 'Host tidak merespon ping';

            $action =
                '📡 Cek koneksi jaringan atau pastikan device menyala';
        }

        $this->saveResult(
            $service,
            $oldStatus,
            $status,
            $code,
            $time,
            $reason,
            $detail,
            $action
        );
    }

    private function saveResult(
        $service,
        $oldStatus,
        $status,
        $code,
        $time,
        $reason,
        $detail,
        $action
    )
    {
        $service->update([

            'last_status' => $status,

            'last_code' => $code,

            'last_response_time' => $time,

            'last_message' => $detail

        ]);

        ServiceLog::create([

            'service_id' => $service->id,

            'status' => $status,

            'response_code' => $code,

            'response_time' => $time,

            'message' => $detail

        ]);

        if ($oldStatus != $status) {

            $this->sendWhatsappAlert(
                $service,
                $status,
                $code,
                $time,
                $reason,
                $detail,
                $action
            );
        }
    }

    private function analyzeResponse($code, $time)
    {
        if ($code == 200) {

            if ($time > 3) {

                return [

                    'status' => 'WARNING',

                    'reason' => 'SLOW_RESPONSE',

                    'detail' => "Response lambat ({$time}s)",

                    'action' => '🐌 Optimasi performa server'

                ];
            }

            return [

                'status' => 'UP',

                'reason' => 'OK',

                'detail' => 'Service normal',

                'action' => '-'

            ];
        }

        if ($code == 404) {

            return [

                'status' => 'DOWN',

                'reason' => 'HTTP_404',

                'detail' => 'Halaman tidak ditemukan',

                'action' => '🔍 Periksa URL endpoint'

            ];
        }

        if ($code == 500) {

            return [

                'status' => 'DOWN',

                'reason' => 'HTTP_500',

                'detail' => 'Internal Server Error',

                'action' => '💥 Periksa log aplikasi'

            ];
        }

        if ($code == 502) {

            return [

                'status' => 'DOWN',

                'reason' => 'HTTP_502',

                'detail' => 'Bad Gateway',

                'action' => '🌉 Periksa proxy atau gateway'

            ];
        }

        if ($code == 503) {

            return [

                'status' => 'DOWN',

                'reason' => 'HTTP_503',

                'detail' => 'Service Unavailable',

                'action' => '🔧 Server maintenance atau overload'

            ];
        }

        return [

            'status' => 'WARNING',

            'reason' => 'HTTP_ERROR',

            'detail' => "HTTP {$code}",

            'action' => '🔧 Periksa service'

        ];
    }

    private function analyzeException($message)
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'timed out')) {

            return [

                'reason' => 'TIMEOUT',

                'detail' => 'Server terlalu lama merespon',

                'action' => '⏱️ Cek performa server'

            ];
        }

        if (str_contains($msg, 'connection refused')) {

            return [

                'reason' => 'CONNECTION_REFUSED',

                'detail' => 'Port tidak menerima koneksi',

                'action' => '🔌 Server mati atau service belum berjalan'

            ];
        }

        if (str_contains($msg, 'could not resolve')) {

            return [

                'reason' => 'DNS_ERROR',

                'detail' => 'DNS tidak ditemukan',

                'action' => '🌐 Periksa DNS dan domain'

            ];
        }

        return [

            'reason' => 'UNKNOWN',

            'detail' => $message,

            'action' => '🔧 Periksa service secara manual'

        ];
    }

    private function sendWhatsappAlert(
        $service,
        $status,
        $code,
        $time,
        $reason,
        $detail,
        $action
    )
    {
        $contacts = Contact::where(
            'is_active',
            true
        )->get();

        if ($status == 'DOWN') {

            $message =
"🔴 SERVICE DOWN

📌 Nama Service : {$service->name}
🔗 URL : {$service->target}

⚠️ Status : DOWN
📟 Code : {$code}

❌ Penyebab :
{$reason}

💡 Detail :
{$detail}

🔧 Tindakan :
{$action}

⏱️ Response :
{$time}s

🕐 ".now()->format('d-m-Y H:i:s');

        } elseif ($status == 'WARNING') {

            $message =
"🟠 SERVICE WARNING

📌 Nama Service : {$service->name}
🔗 URL : {$service->target}

📟 Code : {$code}

❌ Penyebab :
{$reason}

💡 Detail :
{$detail}

🔧 Tindakan :
{$action}

⏱️ Response :
{$time}s

🕐 ".now()->format('d-m-Y H:i:s');

        } else {

            $message =
"🟢 SERVICE NORMAL

📌 Nama Service : {$service->name}
🔗 URL : {$service->target}

✅ Status : UP
📟 Code : {$code}
⏱️ Response : {$time}s

🕐 ".now()->format('d-m-Y H:i:s');
        }

        foreach ($contacts as $contact) {

            FonnteService::send(
                $contact->phone,
                $message
            );
        }
    }
}
