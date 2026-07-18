<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServiceMonitorService
{
    private $networkAlertSent = false;

    public function check(Service $service)
    {
        if ($service->type === 'ping') {
            return $this->checkPing($service);
        }
        return $this->checkHttp($service);
    }

    public function checkNetworkConnection()
    {
        $dnsTargets = ['google.com', '1.1.1.1', '8.8.8.8'];
        foreach ($dnsTargets as $target) {
            if (checkdnsrr($target, 'A')) {
                Log::info('Network check: Connected via DNS - ' . $target);
                return true;
            }
        }

        try {
            $response = Http::timeout(5)->get('https://www.google.com');
            if ($response->successful()) {
                Log::info('Network check: Connected via HTTP');
                return true;
            }
        } catch (\Exception $e) {
            Log::info('Network check: HTTP failed - ' . $e->getMessage());
        }

        try {
            $response = Http::timeout(5)->get('http://8.8.8.8');
            if ($response->successful()) {
                Log::info('Network check: Connected via 8.8.8.8');
                return true;
            }
        } catch (\Exception $e) {
            Log::info('Network check: 8.8.8.8 failed');
        }

        try {
            $response = Http::timeout(5)->get('https://1.1.1.1');
            if ($response->successful()) {
                Log::info('Network check: Connected via 1.1.1.1');
                return true;
            }
        } catch (\Exception $e) {
            Log::info('Network check: 1.1.1.1 failed');
        }

        Log::info('Network check: DISCONNECTED');
        return false;
    }

    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status;
        $code = null;
        $time = 0;

        $isNetworkConnected = $this->checkNetworkConnection();
        $this->handleNetworkStatus($isNetworkConnected);

        if (!$isNetworkConnected) {
            Log::info("⏭️ Skip check {$service->name} karena jaringan terputus");
            $service->update(['last_check_at' => now()]);
            return;
        }

        try {
            $url = $this->normalizeUrl($service->target);
            $start = microtime(true);

            $response = Http::timeout(20)
                ->connectTimeout(12)
                ->withoutRedirecting()
                ->get($url);

            $time = round(microtime(true) - $start, 2);
            $code = $response->status();

            Log::info("📊 HTTP Response {$service->name}: code={$code}, time={$time}s");

            $analysis = $this->analyzeResponseByCode($code, $response->body(), $time);
            Log::info("📝 Analysis {$service->name}: " . json_encode($analysis));

        } catch (ConnectionException $e) {
            $time = round(microtime(true) - $start, 2);
            $code = 'TIMEOUT';
            $analysis = [
                'status' => 'DOWN',
                'reason' => 'CONNECTION_TIMEOUT',
                'detail' => 'Koneksi timeout - Pengguna TIDAK bisa akses',
                'action' => 'Cek firewall, pastikan server menyala'
            ];
            Log::error("Connection timeout {$service->name}: " . $e->getMessage());
            
        } catch (\Exception $e) {
            $time = 0;
            $code = 'ERROR';
            $analysis = $this->analyzeException($e->getMessage());
            Log::error("HTTP Error {$service->name}: " . $e->getMessage());
        }

        $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, $analysis['reason'], $analysis['detail'], $analysis['action']);
    }

    private function checkPing(Service $service)
    {
        $oldStatus = $service->last_status;
        $code = 'N/A';
        $time = 0;

        $isNetworkConnected = $this->checkNetworkConnection();
        $this->handleNetworkStatus($isNetworkConnected);

        if (!$isNetworkConnected) {
            Log::info("⏭️ Skip ping check {$service->name} karena jaringan terputus");
            $service->update(['last_check_at' => now()]);
            return;
        }

        $target = $service->target;
        $parts = explode(':', $target);
        $host = $parts[0];
        $port = isset($parts[1]) ? (int)$parts[1] : null;
        $start = microtime(true);

        if ($port) {
            if ($port < 1 || $port > 65535) {
                $time = round(microtime(true) - $start, 2);
                $code = 'INVALID_PORT';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'INVALID_PORT', "Port {$port} tidak valid", 'Periksa format port');
                return;
            }

            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            $time = round(microtime(true) - $start, 2);

            if ($connection) {
                fclose($connection);
                $code = 'PORT_OPEN';
                $this->saveResult($service, $oldStatus, 'UP', $code, $time, 'PORT_OK', "Host {$host} merespon port {$port}", '-');
            } else {
                $code = 'PORT_CLOSED';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'PORT_CLOSED', "Port {$port} tidak merespon", 'Periksa firewall');
            }
            return;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            exec("ping -n 1 " . escapeshellarg($host), $output, $result);
        } else {
            if (checkdnsrr($host, 'A') || checkdnsrr($host, 'AAAA')) {
                exec("ping -n 1 " . escapeshellarg($host), $output, $result);
            } else {
                $time = round(microtime(true) - $start, 2);
                $code = 'DNS_ERROR';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'DNS_ERROR', "Hostname {$host} tidak dapat di-resolve", 'Periksa DNS');
                return;
            }
        }

        $time = round(microtime(true) - $start, 2);

        if ($result === 0) {
            $code = 'PING_OK';
            $this->saveResult($service, $oldStatus, 'UP', $code, $time, 'PING_OK', 'Host merespon ping', '-');
        } else {
            $code = 'PING_FAILED';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'PING_FAILED', 'Host tidak merespon ping', 'Cek koneksi jaringan');
        }
    }

    private function analyzeResponseByCode($code, $body, $time)
    {
        if (empty($body) || trim($body) === '') {
            Log::warning("⚠️ Response kosong: code={$code}, service body empty");
            
            if ($code >= 200 && $code < 300) {
                return [
                    'status' => 'WARNING',
                    'reason' => 'EMPTY_RESPONSE',
                    'detail' => 'Halaman merespon tapi konten kosong - Pengguna bisa akses tapi tidak ada konten',
                    'action' => 'Periksa apakah halaman memang kosong atau ada error'
                ];
            }
            
            return [
                'status' => 'DOWN',
                'reason' => 'EMPTY_RESPONSE_ERROR',
                'detail' => "Server error ({$code}) dengan response kosong - Pengguna TIDAK bisa akses",
                'action' => 'Cek log server, periksa error di aplikasi'
            ];
        }

        $errorKeywords = [
            'fatal error', 'parse error', 'syntax error',
            'exception', 'stack trace',
            'connection refused', 'database error', 'sql error',
            'permission denied'
        ];

        $bodyLower = strtolower($body);
        foreach ($errorKeywords as $keyword) {
            if (str_contains($bodyLower, $keyword)) {
                Log::warning("⚠️ Konten mengandung error: '{$keyword}'");
                return [
                    'status' => 'DOWN',
                    'reason' => 'ERROR_IN_CONTENT',
                    'detail' => "Konten error: '{$keyword}' - Pengguna TIDAK bisa akses",
                    'action' => 'Periksa log server'
                ];
            }
        }

        return $this->analyzeResponse($code, $time);
    }

    private function analyzeResponse($code, $time)
    {
        if ($code >= 200 && $code < 300) {
            if ($time > 8) {
                return [
                    'status' => 'WARNING',
                    'reason' => 'SLOW_RESPONSE',
                    'detail' => "Response lambat ({$time}s) - Pengguna masih bisa akses tapi lambat",
                    'action' => 'Optimasi performa server'
                ];
            }

            return [
                'status' => 'UP',
                'reason' => 'HTTP_' . $code,
                'detail' => 'Service berjalan normal - Pengguna bisa akses',
                'action' => '-'
            ];
        }

        if ($code >= 300 && $code < 400) {
            $redirectCodes = [
                301 => 'Redirect permanen',
                302 => 'Redirect sementara',
                303 => 'See Other',
                307 => 'Temporary Redirect',
                308 => 'Permanent Redirect'
            ];

            return [
                'status' => 'UP',
                'reason' => 'HTTP_' . $code,
                'detail' => $redirectCodes[$code] ?? 'Redirect - Pengguna tetap bisa akses',
                'action' => in_array($code, [301, 308]) ? 'Update URL endpoint' : 'Periksa redirect jika mengganggu'
            ];
        }

        if ($code >= 400 && $code < 500) {
            $clientErrors = [
                400 => ['status' => 'WARNING', 'reason' => 'HTTP_400', 'detail' => 'Bad Request - Pengguna bisa akses dengan perbaikan', 'action' => 'Periksa format request'],
                405 => ['status' => 'WARNING', 'reason' => 'HTTP_405', 'detail' => 'Method HTTP tidak diizinkan', 'action' => 'Ganti method HTTP'],
                429 => ['status' => 'WARNING', 'reason' => 'HTTP_429', 'detail' => 'Too Many Requests - Rate limit', 'action' => 'Kurangi frekuensi request'],
                401 => ['status' => 'UP', 'reason' => 'HTTP_401', 'detail' => 'Unauthorized - Pengguna perlu login - Masih bisa akses', 'action' => 'Pastikan kredensial benar'],
                403 => ['status' => 'UP', 'reason' => 'HTTP_403', 'detail' => 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'action' => 'Cek izin akses'],
                404 => ['status' => 'DOWN', 'reason' => 'HTTP_404', 'detail' => 'Halaman tidak ditemukan - Pengguna TIDAK bisa akses', 'action' => 'Periksa URL endpoint'],
                408 => ['status' => 'DOWN', 'reason' => 'HTTP_408', 'detail' => 'Request Timeout - Pengguna TIDAK bisa akses', 'action' => 'Cek performa server'],
                410 => ['status' => 'DOWN', 'reason' => 'HTTP_410', 'detail' => 'Gone - Resource sudah tidak tersedia', 'action' => 'Update URL atau hapus monitoring'],
                415 => ['status' => 'DOWN', 'reason' => 'HTTP_415', 'detail' => 'Unsupported Media Type', 'action' => 'Periksa header Content-Type'],
            ];

            if (isset($clientErrors[$code])) {
                return $clientErrors[$code];
            }

            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_' . $code,
                'detail' => "Client Error {$code} - Pengguna TIDAK bisa akses",
                'action' => 'Periksa request yang dikirim'
            ];
        }

        if ($code >= 500 && $code < 600) {
            $serverErrors = [
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout'
            ];

            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_' . $code,
                'detail' => ($serverErrors[$code] ?? "Server Error {$code}") . ' - Pengguna TIDAK bisa akses',
                'action' => $this->getServerErrorAction($code)
            ];
        }

        return [
            'status' => 'DOWN',
            'reason' => 'HTTP_UNKNOWN',
            'detail' => "HTTP {$code} - Kode tidak dikenal - Pengguna TIDAK bisa akses",
            'action' => 'Periksa dokumentasi API'
        ];
    }

    private function getServerErrorAction($code)
    {
        $actions = [
            500 => 'Cek log server, periksa kode aplikasi',
            501 => 'Periksa konfigurasi server',
            502 => 'Periksa proxy / load balancer',
            503 => 'Cek maintenance / scale up resource',
            504 => 'Optimasi response time server'
        ];

        return $actions[$code] ?? 'Periksa log server dan konfigurasi';
    }

    private function analyzeException($message)
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'connection timeout') || str_contains($msg, 'timed out')) {
            return ['status' => 'DOWN', 'reason' => 'CONNECTION_TIMEOUT', 'detail' => 'Koneksi timeout - Pengguna TIDAK bisa akses', 'action' => 'Cek firewall, pastikan server menyala'];
        }

        if (str_contains($msg, 'connection refused')) {
            return ['status' => 'DOWN', 'reason' => 'CONNECTION_REFUSED', 'detail' => 'Koneksi ditolak - Pengguna TIDAK bisa akses', 'action' => 'Server mati / firewall blocking'];
        }

        if (str_contains($msg, 'could not resolve') || str_contains($msg, 'dns')) {
            return ['status' => 'DOWN', 'reason' => 'DNS_ERROR', 'detail' => 'DNS tidak ditemukan - Pengguna TIDAK bisa akses', 'action' => 'Periksa DNS / domain'];
        }

        if (str_contains($msg, 'no route to host') || str_contains($msg, 'network is unreachable')) {
            return ['status' => 'DOWN', 'reason' => 'HOST_UNREACHABLE', 'detail' => 'Host tidak dapat dijangkau - Pengguna TIDAK bisa akses', 'action' => 'Cek koneksi jaringan / firewall'];
        }

        if (str_contains($msg, 'curl error')) {
            return ['status' => 'DOWN', 'reason' => 'CURL_ERROR', 'detail' => 'Error koneksi - Pengguna TIDAK bisa akses', 'action' => 'Periksa konfigurasi server'];
        }

        if (str_contains($msg, 'ssl') || str_contains($msg, 'certificate')) {
            return ['status' => 'WARNING', 'reason' => 'SSL_ERROR', 'detail' => 'SSL Error - Pengguna mungkin masih bisa akses', 'action' => 'Periksa sertifikat SSL'];
        }

        return [
            'status' => 'DOWN',
            'reason' => 'UNKNOWN_ERROR',
            'detail' => 'Error tidak dikenal - Pengguna TIDAK bisa akses: ' . $message,
            'action' => 'Periksa service secara manual'
        ];
    }

    /**
     * ============================================================
     * 🔥🔥🔥 SAVE RESULT - VERSI FINAL (DIPERBAIKI) 🔥🔥🔥
     * ============================================================
     * 
     * PERBAIKAN:
     * 1. First Check dicek SEBELUM log dibuat
     * 2. First Check UP → TIDAK kirim WA
     * 3. First Check DOWN/WARNING → KIRIM WA
     * 4. Interval logic berjalan normal setelah first check
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        if ($code === null || $code === '') {
            Log::warning("⚠️ Code is null/empty for service {$service->name}, setting to 'N/A'");
            $code = 'N/A';
        }

        // ============================================================
        // 🔥🔥🔥 CEK FIRST CHECK SEBELUM UPDATE STATUS! 🔥🔥🔥
        // ============================================================
        $existingLogs = ServiceLog::where('service_id', $service->id)->count();
        $isFirstCheck = ($existingLogs === 0);
        
        Log::info("📊 First check status: " . ($isFirstCheck ? 'YES' : 'NO') . " for {$service->name}");

        // ============================================================
        // 📌 UPDATE STATUS SERVICE
        // ============================================================
        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail,
            'last_check_at' => now(),
        ]);

        // ============================================================
        // 📌 BUAT LOG JIKA STATUS BERUBAH
        // ============================================================
        if ($oldStatus != $status) {
            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $status,
                'response_code' => $code,
                'response_time' => $time,
                'message' => $detail,
                'action' => $action,
                'checked_at' => now(),
            ]);
            Log::info("📝 Log: {$service->name} {$oldStatus} → {$status}");
        }

        // ============================================================
        // 📌 LOGIKA FIRST CHECK (HANYA 1 KALI) - PRIORITAS UTAMA!
        // ============================================================
        if ($isFirstCheck) {
            Log::info("🆕 FIRST CHECK: {$service->name} - Pertama kali di-monitoring");
            
            if ($status === 'DOWN' || $status === 'WARNING') {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => $status,
                ]);
                Log::info("📱 First check WA: {$service->name} → {$status}");
            } else {
                Log::info("⏭️ First check UP: {$service->name} - Tidak perlu notifikasi");
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => 'UP',
                ]);
            }
            
            // 🔥 LANGSUNG KELUAR, TIDAK PROSES INTERVAL!
            $lastLog = ServiceLog::where('service_id', $service->id)->latest()->first();
            if ($lastLog) {
                $lastLog->update(['checked_at' => now()]);
            }
            return;
        }

        // ============================================================
        // 🔥 BUKAN FIRST CHECK: Logika normal dengan interval
        // ============================================================
        $interval = session('wa_interval', 0);
        
        if (empty($service->last_wa_sent_at)) {
            $service->update([
                'last_wa_sent_at' => now()->subMinutes($interval),
            ]);
            $service->refresh();
            Log::info("📝 Set last_wa_sent_at ke: " . now()->subMinutes($interval) . " (karena NULL)");
        }

        // ============================================================
        // 📌 CEK INTERVAL & KIRIM WA (HANYA JIKA STATUS BERUBAH)
        // ============================================================
        if ($oldStatus != $status) {
            if ($this->isIntervalMet($service, $interval)) {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => $status,
                ]);
                
                Log::info("📱 WA terkirim: {$service->name} {$oldStatus} → {$status} (interval tercapai)");
            } else {
                Log::info("⏭️ Skip WA: {$service->name} - Status berubah {$oldStatus} → {$status} (interval BELUM tercapai)");
            }
        } else {
            Log::info("⏭️ Skip WA: {$service->name} - Status sama ({$status})");
        }

        $lastLog = ServiceLog::where('service_id', $service->id)->latest()->first();
        if ($lastLog) {
            $lastLog->update(['checked_at' => now()]);
        }
    }

    /**
     * ============================================================
     * 🔥 CEK APAKAH INTERVAL SUDAH TERPENUHI?
     * ============================================================
     */
    private function isIntervalMet(Service $service, int $interval): bool
    {
        if ($interval <= 0) {
            Log::info("✅ Interval 0, selalu kirim WA");
            return true;
        }

        $lastWaSent = $service->last_wa_sent_at;
        
        if (empty($lastWaSent)) {
            $service->update([
                'last_wa_sent_at' => now()->subMinutes($interval),
            ]);
            $service->refresh();
            $lastWaSent = $service->last_wa_sent_at;
            Log::info("📝 Set last_wa_sent_at ke: {$lastWaSent} (karena NULL)");
        }

        $lastSent = Carbon::parse($lastWaSent);
        $minutesSinceLastWa = $lastSent->diffInRealMinutes(now());
        
        Log::info("⏱️ WA terakhir: {$minutesSinceLastWa} menit yang lalu, interval: {$interval} menit");

        if ($minutesSinceLastWa >= $interval) {
            Log::info("✅ Interval {$interval} menit TERPENUHI, boleh kirim WA");
            return true;
        }

        Log::info("⏭️ Interval BELUM terpenuhi ({$minutesSinceLastWa} < {$interval} menit), SKIP WA");
        return false;
    }

    /**
     * ============================================================
     * 🔥 KIRIM WHATSAPP
     * ============================================================
     */
    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif');
            return;
        }

        if ($status == 'DOWN') {
            $message = "🔴 SERVICE DOWN\n\n📌 {$service->name}\n🔗 {$service->target}\n\nStatus : DOWN ❌\nCode   : {$code}\nWaktu  : {$time}s\n\n💡 {$detail}\n🔧 {$action}\n\n🕐 " . now()->format('d-m-Y H:i:s');
        } elseif ($status == 'WARNING') {
            $message = "🟠 SERVICE WARNING\n\n📌 {$service->name}\n🔗 {$service->target}\n\nStatus : WARNING ⚠️\nCode   : {$code}\nWaktu  : {$time}s\n\n💡 {$detail}\n🔧 {$action}\n\n🕐 " . now()->format('d-m-Y H:i:s');
        } else {
            $message = "🟢 SERVICE NORMAL\n\n📌 {$service->name}\n🔗 {$service->target}\n\nStatus : UP ✅\nCode   : {$code}\nWaktu  : {$time}s\n\n🕐 " . now()->format('d-m-Y H:i:s');
        }

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "📱 WA ke: {$contact->phone} - {$status}" : "❌ Gagal WA ke: {$contact->phone}");
        }
    }

    private function normalizeUrl($url)
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return 'https://' . $url;
        }
        return $url;
    }

    private function handleNetworkStatus($isNetworkConnected)
    {
        if (!$isNetworkConnected && !$this->networkAlertSent) {
            Log::info('📡 Network: DISCONNECTED');
            $this->networkAlertSent = true;
        }
        if ($isNetworkConnected && $this->networkAlertSent) {
            Log::info('📡 Network: RESTORED');
            $this->networkAlertSent = false;
        }
    }

    private function getStatusGroup($code)
    {
        if ($code === 'N/A' || $code === 'PING' || $code === 'PORT_OPEN') return 'CONNECTION';
        if ($code >= 200 && $code < 300) return 'SUCCESS';
        if ($code >= 300 && $code < 400) return 'REDIRECTION';
        if ($code >= 400 && $code < 500) return 'CLIENT_ERROR';
        if ($code >= 500 && $code < 600) return 'SERVER_ERROR';
        return 'UNKNOWN';
    }
}