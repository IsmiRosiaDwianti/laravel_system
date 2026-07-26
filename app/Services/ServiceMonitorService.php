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
            Log::info("Skip check {$service->name} karena jaringan terputus");
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

            Log::info("HTTP Response {$service->name}: code={$code}, time={$time}s");

            $analysis = $this->analyzeResponseByCode($code, $response->body(), $time);
            Log::info("Analysis {$service->name}: " . json_encode($analysis));

        } catch (ConnectionException $e) {
            $time = round(microtime(true) - $start, 2);
            $code = 'TIMEOUT';
            $analysis = [
                'status' => 'DOWN',
                'reason' => 'CONNECTION_TIMEOUT',
                'detail' => 'Koneksi timeout - Pengguna tidak bisa akses',
                'action' => 'Periksa firewall dan pastikan server menyala'
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
            Log::info("Skip ping check {$service->name} karena jaringan terputus");
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
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'INVALID_PORT', "Port {$port} tidak valid", 'Periksa format port (1-65535)');
                return;
            }

            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            $time = round(microtime(true) - $start, 2);

            if ($connection) {
                fclose($connection);
                $code = 'PORT_OPEN';
                $this->saveResult($service, $oldStatus, 'UP', $code, $time, 'PORT_OK', "Host {$host} merespon port {$port}", 'Port terbuka, service berjalan normal');
            } else {
                $code = 'PORT_CLOSED';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'PORT_CLOSED', "Port {$port} tidak merespon", 'Periksa firewall dan pastikan service berjalan di port tersebut');
            }
            return;
        }

        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            if (!checkdnsrr($host, 'A') && !checkdnsrr($host, 'AAAA')) {
                $time = round(microtime(true) - $start, 2);
                $code = 'DNS_ERROR';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'DNS_ERROR', "Hostname {$host} tidak dapat di-resolve", 'Periksa konfigurasi DNS server');
                return;
            }
        }

        $start = microtime(true);
        
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            $command = "ping -n 2 -w 5000 " . escapeshellarg($host) . " 2>&1";
        } else {
            $command = "ping -c 2 -W 5 " . escapeshellarg($host) . " 2>&1";
        }
        
        exec($command, $output, $resultCode);
        $outputString = implode("\n", $output);
        
        $time = round(microtime(true) - $start, 2);
        
        Log::info("Ping result for {$host}:", [
            'resultCode' => $resultCode,
            'time' => $time . 's',
            'output' => $outputString
        ]);

        if (strpos($outputString, 'Destination host unreachable') !== false ||
            strpos($outputString, 'Host unreachable') !== false ||
            strpos($outputString, 'unreachable') !== false) {
            $code = 'UNREACHABLE';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'HOST_UNREACHABLE', 
                'Host tidak dapat dijangkau', 
                'Periksa koneksi jaringan, firewall, dan routing'
            );
            Log::warning("Host UNREACHABLE: {$host}");
            return;
        }

        if (strpos($outputString, 'Network is unreachable') !== false ||
            strpos($outputString, 'network unreachable') !== false) {
            $code = 'NETWORK_UNREACHABLE';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'NETWORK_UNREACHABLE', 
                'Jaringan tidak dapat menjangkau host', 
                'Periksa routing dan konfigurasi firewall'
            );
            Log::warning("NETWORK UNREACHABLE: {$host}");
            return;
        }

        if (strpos($outputString, 'Request timed out') !== false ||
            strpos($outputString, 'timeout') !== false ||
            strpos($outputString, 'Timed out') !== false) {
            
            preg_match('/(\d+)\s*received/i', $outputString, $receivedMatches);
            $received = isset($receivedMatches[1]) ? intval($receivedMatches[1]) : 0;
            
            if ($received > 0) {
                $code = 'PING_PARTIAL';
                $this->saveResult(
                    $service, 
                    $oldStatus, 
                    'WARNING',
                    $code, 
                    $time, 
                    'PING_PARTIAL', 
                    "Ping timeout ({$received}/2 berhasil) - Host merespon lambat", 
                    'Packet loss terdeteksi, periksa kualitas jaringan'
                );
                Log::warning("PING PARTIAL: {$host} - {$received}/2 berhasil");
                return;
            }
            
            $code = 'TIMEOUT';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'PING_TIMEOUT', 
                'Request timeout - Host tidak merespon', 
                'Periksa firewall dan pastikan host menyala'
            );
            Log::warning("PING TIMEOUT: {$host}");
            return;
        }

        if (strpos($outputString, 'TTL expired') !== false ||
            strpos($outputString, 'TTL Exceeded') !== false) {
            $code = 'TTL_EXPIRED';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'TTL_EXPIRED', 
                'TTL expired - Hop terlalu jauh', 
                'Periksa routing jaringan, mungkin ada loop atau hop terlalu banyak'
            );
            Log::warning("TTL EXPIRED: {$host}");
            return;
        }

        if (strpos($outputString, 'General failure') !== false) {
            $code = 'GENERAL_FAILURE';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'GENERAL_FAILURE', 
                'General failure - Masalah jaringan lokal', 
                'Periksa adapter jaringan dan konfigurasi firewall'
            );
            Log::warning("GENERAL FAILURE: {$host}");
            return;
        }

        if (strpos($outputString, 'Destination net unreachable') !== false) {
            $code = 'NET_UNREACHABLE';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'NET_UNREACHABLE', 
                'Destination net unreachable', 
                'Periksa routing dan konfigurasi firewall'
            );
            Log::warning("DESTINATION NET UNREACHABLE: {$host}");
            return;
        }

        if (strpos($outputString, 'Destination port unreachable') !== false) {
            $code = 'PORT_UNREACHABLE';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'PORT_UNREACHABLE', 
                'Destination port unreachable', 
                'Periksa firewall dan service di port tersebut'
            );
            Log::warning("DESTINATION PORT UNREACHABLE: {$host}");
            return;
        }

        preg_match('/(\d+)%\s*loss/i', $outputString, $lossMatches);
        if (isset($lossMatches[1])) {
            $loss = intval($lossMatches[1]);
            if ($loss >= 50) {
                $code = 'HIGH_PACKET_LOSS';
                $this->saveResult(
                    $service, 
                    $oldStatus, 
                    'WARNING', 
                    $code, 
                    $time, 
                    'HIGH_PACKET_LOSS', 
                    "Packet loss {$loss}% - Koneksi tidak stabil", 
                    'Kualitas jaringan buruk, periksa kabel/switch/router'
                );
                Log::warning("HIGH PACKET LOSS: {$host} - {$loss}%");
                return;
            }
            
            if ($loss > 0 && $loss < 50) {
                $code = 'PACKET_LOSS';
                $this->saveResult(
                    $service, 
                    $oldStatus, 
                    'WARNING', 
                    $code, 
                    $time, 
                    'PACKET_LOSS', 
                    "Packet loss {$loss}% - Koneksi kurang stabil", 
                    'Periksa kualitas jaringan, mungkin ada interferensi'
                );
                Log::warning("PACKET LOSS: {$host} - {$loss}%");
                return;
            }
        }

        if (strpos($outputString, 'could not find host') !== false ||
            strpos($outputString, 'Unknown host') !== false) {
            $code = 'DNS_ERROR';
            $this->saveResult(
                $service, 
                $oldStatus, 
                'DOWN', 
                $code, 
                $time, 
                'DNS_ERROR', 
                "Hostname {$host} tidak dapat di-resolve", 
                'Periksa konfigurasi DNS server'
            );
            Log::warning("DNS ERROR: {$host}");
            return;
        }

        if ($resultCode === 0) {
            preg_match_all('/(?:time[=:]\s*)(\d+\.?\d*)\s*ms/i', $outputString, $matches);
            
            $avgTime = 0;
            $minTime = 0;
            $maxTime = 0;
            
            if (!empty($matches[1])) {
                $times = array_map('floatval', $matches[1]);
                $avgTime = round(array_sum($times) / count($times) / 1000, 3);
                $minTime = round(min($times) / 1000, 3);
                $maxTime = round(max($times) / 1000, 3);
            }
            
            $code = 'PING_OK';
            
            if ($avgTime > 3) {
                $this->saveResult(
                    $service, 
                    $oldStatus, 
                    'WARNING',
                    $code, 
                    $avgTime > 0 ? $avgTime : $time, 
                    'PING_OK_SLOW', 
                    "Host merespon tapi lambat (avg: {$avgTime}s, min: {$minTime}s, max: {$maxTime}s)", 
                    'Response lambat, optimasi jaringan atau server'
                );
                Log::info("PING OK (SLOW): {$host} - avg: {$avgTime}s");
            } else {
                $this->saveResult(
                    $service, 
                    $oldStatus, 
                    'UP', 
                    $code, 
                    $avgTime > 0 ? $avgTime : $time, 
                    'PING_OK', 
                    "Host merespon ping (avg: {$avgTime}s, min: {$minTime}s, max: {$maxTime}s)", 
                    'Service dalam kondisi baik, tidak perlu tindakan'
                );
                Log::info("PING OK: {$host} - avg: {$avgTime}s");
            }
            return;
        }

        $code = 'PING_FAILED';
        $this->saveResult(
            $service, 
            $oldStatus, 
            'DOWN', 
            $code, 
            $time, 
            'PING_FAILED', 
            'Host tidak merespon ping (unknown reason)', 
            'Periksa koneksi jaringan dan konfigurasi firewall'
        );
        Log::warning("PING FAILED (unknown): {$host}");
    }

    private function analyzeResponseByCode($code, $body, $time)
    {
        if (empty($body) || trim($body) === '') {
            Log::warning("Response kosong: code={$code}, service body empty");
            
            if ($code >= 200 && $code < 300) {
                return [
                    'status' => 'WARNING',
                    'reason' => 'EMPTY_RESPONSE',
                    'detail' => 'Halaman merespon tapi konten kosong - Pengguna bisa akses tapi tidak ada konten',
                    'action' => 'Periksa apakah halaman memang kosong atau ada error di aplikasi'
                ];
            }
            
            return [
                'status' => 'DOWN',
                'reason' => 'EMPTY_RESPONSE_ERROR',
                'detail' => "Server error ({$code}) dengan response kosong - Pengguna tidak bisa akses",
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
                Log::warning("Konten mengandung error: '{$keyword}'");
                return [
                    'status' => 'DOWN',
                    'reason' => 'ERROR_IN_CONTENT',
                    'detail' => "Konten error: '{$keyword}' - Pengguna tidak bisa akses",
                    'action' => 'Periksa log server dan perbaiki error aplikasi'
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
                    'action' => 'Optimasi performa server, response time terlalu lama'
                ];
            }

            return [
                'status' => 'UP',
                'reason' => 'HTTP_' . $code,
                'detail' => 'Service berjalan normal - Pengguna bisa akses',
                'action' => 'Service dalam kondisi baik, tidak perlu tindakan'
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
                'action' => in_array($code, [301, 308]) ? 'Update URL endpoint (redirect permanen)' : 'Periksa redirect jika mengganggu akses'
            ];
        }

        if ($code >= 400 && $code < 500) {
            $clientErrors = [
                400 => ['status' => 'WARNING', 'reason' => 'HTTP_400', 'detail' => 'Bad Request - Pengguna bisa akses dengan perbaikan', 'action' => 'Periksa format request yang dikirim'],
                405 => ['status' => 'WARNING', 'reason' => 'HTTP_405', 'detail' => 'Method HTTP tidak diizinkan', 'action' => 'Ganti method HTTP yang digunakan'],
                429 => ['status' => 'WARNING', 'reason' => 'HTTP_429', 'detail' => 'Too Many Requests - Rate limit', 'action' => 'Kurangi frekuensi request, tunggu beberapa saat'],
                401 => ['status' => 'UP', 'reason' => 'HTTP_401', 'detail' => 'Unauthorized - Pengguna perlu login - Masih bisa akses', 'action' => 'Pastikan kredensial login benar'],
                403 => ['status' => 'UP', 'reason' => 'HTTP_403', 'detail' => 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'action' => 'Periksa izin akses pengguna'],
                404 => ['status' => 'DOWN', 'reason' => 'HTTP_404', 'detail' => 'Halaman tidak ditemukan - Pengguna tidak bisa akses', 'action' => 'Periksa URL endpoint, mungkin sudah berubah'],
                408 => ['status' => 'DOWN', 'reason' => 'HTTP_408', 'detail' => 'Request Timeout - Pengguna tidak bisa akses', 'action' => 'Cek performa server, mungkin overload'],
                410 => ['status' => 'DOWN', 'reason' => 'HTTP_410', 'detail' => 'Gone - Resource sudah tidak tersedia', 'action' => 'Update URL atau hapus monitoring jika sudah tidak digunakan'],
                415 => ['status' => 'DOWN', 'reason' => 'HTTP_415', 'detail' => 'Unsupported Media Type', 'action' => 'Periksa header Content-Type yang dikirim'],
            ];

            if (isset($clientErrors[$code])) {
                return $clientErrors[$code];
            }

            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_' . $code,
                'detail' => "Client Error {$code} - Pengguna tidak bisa akses",
                'action' => 'Periksa request yang dikirim ke server'
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
                'detail' => ($serverErrors[$code] ?? "Server Error {$code}") . ' - Pengguna tidak bisa akses',
                'action' => $this->getServerErrorAction($code)
            ];
        }

        return [
            'status' => 'DOWN',
            'reason' => 'HTTP_UNKNOWN',
            'detail' => "HTTP {$code} - Kode tidak dikenal - Pengguna tidak bisa akses",
            'action' => 'Periksa dokumentasi API untuk kode status ini'
        ];
    }

    private function getServerErrorAction($code)
    {
        $actions = [
            500 => 'Cek log server, periksa kode aplikasi yang error',
            501 => 'Periksa konfigurasi server, fitur mungkin belum diimplementasikan',
            502 => 'Periksa proxy / load balancer, mungkin ada masalah koneksi',
            503 => 'Cek maintenance server, atau scale up resource jika overload',
            504 => 'Optimasi response time server, mungkin gateway timeout'
        ];

        return $actions[$code] ?? 'Periksa log server dan konfigurasi';
    }

    private function analyzeException($message)
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'connection timeout') || str_contains($msg, 'timed out')) {
            return ['status' => 'DOWN', 'reason' => 'CONNECTION_TIMEOUT', 'detail' => 'Koneksi timeout - Pengguna tidak bisa akses', 'action' => 'Cek firewall, pastikan server menyala'];
        }

        if (str_contains($msg, 'connection refused')) {
            return ['status' => 'DOWN', 'reason' => 'CONNECTION_REFUSED', 'detail' => 'Koneksi ditolak - Pengguna tidak bisa akses', 'action' => 'Server mati atau firewall blocking koneksi'];
        }

        if (str_contains($msg, 'could not resolve') || str_contains($msg, 'dns')) {
            return ['status' => 'DOWN', 'reason' => 'DNS_ERROR', 'detail' => 'DNS tidak ditemukan - Pengguna tidak bisa akses', 'action' => 'Periksa konfigurasi DNS / domain'];
        }

        if (str_contains($msg, 'no route to host') || str_contains($msg, 'network is unreachable')) {
            return ['status' => 'DOWN', 'reason' => 'HOST_UNREACHABLE', 'detail' => 'Host tidak dapat dijangkau - Pengguna tidak bisa akses', 'action' => 'Periksa koneksi jaringan dan routing'];
        }

        if (str_contains($msg, 'curl error')) {
            return ['status' => 'DOWN', 'reason' => 'CURL_ERROR', 'detail' => 'Error koneksi - Pengguna tidak bisa akses', 'action' => 'Periksa konfigurasi server dan koneksi internet'];
        }

        if (str_contains($msg, 'ssl') || str_contains($msg, 'certificate')) {
            return ['status' => 'WARNING', 'reason' => 'SSL_ERROR', 'detail' => 'SSL Error - Pengguna mungkin masih bisa akses', 'action' => 'Periksa sertifikat SSL, mungkin sudah expired'];
        }

        return [
            'status' => 'DOWN',
            'reason' => 'UNKNOWN_ERROR',
            'detail' => 'Error tidak dikenal - Pengguna tidak bisa akses: ' . $message,
            'action' => 'Periksa service secara manual dan cek log error'
        ];
    }

    /**
     * ============================================================
     * SAVE RESULT - UPDATED WITH is_status_change & previous_status
     * ============================================================
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        if ($code === null || $code === '') {
            Log::warning("Code is null/empty for service {$service->name}, setting to 'N/A'");
            $code = 'N/A';
        }

        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail,
            'last_check_at' => now(),
        ]);

        $statusChanged = ($oldStatus != $status);
        $isFirstCheck = empty($oldStatus) || $oldStatus === 'UNKNOWN' || empty($service->last_wa_sent_at);

        // ✅ SELALU SIMPAN LOG (BUKAN HANYA SAAT STATUS BERUBAH)
        // Biar ada history lengkap, bukan cuma perubahan status
        ServiceLog::create([
            'service_id' => $service->id,
            'status' => $status,
            'response_code' => $code,
            'response_time' => $time,
            'message' => $detail,
            'action' => $action,
            'checked_at' => now(),
            'is_status_change' => $statusChanged,   // ✅ TAMBAHKAN INI
            'previous_status' => $oldStatus,        // ✅ TAMBAHKAN INI
        ]);

        if ($statusChanged) {
            Log::info("🔄 STATUS BERUBAH: {$service->name} {$oldStatus} → {$status}, Code: {$code}");
        } else {
            Log::info("📝 LOG TERSIMPAN: {$service->name} status tetap {$status}, Code: {$code}");
        }

        $this->handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck);
    }

    /**
     * ============================================================
     * 🔄 HANDLE INTERVAL LOGIC
     * ============================================================
     * LOGIKA:
     * 1. FIRST CHECK: Jika status DOWN/WARNING → LANGSUNG KIRIM WA
     * 2. Timer TIDAK RESET saat status berubah di tengah interval
     * 3. Status awal TETAP dari awal interval
     * 4. Interval berubah → Timer RESET ke 0, status awal TETAP
     * 5. DOWN→DOWN atau UP→UP = TIDAK KIRIM
     * 6. DOWN→UP atau UP→DOWN = KIRIM
     * ============================================================
     */
    private function handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck = false)
    {
        $interval = $service->wa_interval_minutes ?? 0;
        
        Log::info("🔍 INTERVAL CHECK: {$service->name} | Interval: {$interval} menit | Status: {$status} | Old: {$oldStatus} | First: {$isFirstCheck}");
        
        // FIRST CHECK: Service baru DOWN/WARNING → LANGSUNG KIRIM
        if ($isFirstCheck && ($status === 'DOWN' || $status === 'WARNING')) {
            Log::info("🚨 FIRST CHECK: Service baru dengan status {$status} - LANGSUNG KIRIM WA");
            $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            $service->update(['last_wa_sent_at' => now()]);
            
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
            return;
        }
        
        // FIRST CHECK: Service baru UP → TIDAK KIRIM
        if ($isFirstCheck && $status === 'UP') {
            Log::info("⏭️ FIRST CHECK: Service baru dengan status UP - TIDAK KIRIM WA");
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'interval_wa_sent_in_this_cycle' => 0,
                'last_wa_sent_at' => now(),
            ]);
            return;
        }

        // INTERVAL = 0 → KIRIM LANGSUNG
        if ($interval == 0) {
            Log::info("⏭️ Interval 0 - Kirim WA langsung saat status berubah");
            
            if ($oldStatus !== $status) {
                if ($status === 'UP') {
                    $this->sendRestoredAlert($service, $oldStatus, $code, $time, $detail);
                } else {
                    $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                }
                $service->update(['last_wa_sent_at' => now()]);
                Log::info("✅ WA terkirim (interval 0): {$service->name} {$oldStatus} → {$status}");
            }
            return;
        }

        // ============================================================
        // INTERVAL > 0
        // ============================================================
        
        $lastIntervalCheck = $service->last_interval_checked_at;
        $lastIntervalStatus = $service->last_interval_status;
        $lastIntervalValue = $service->last_interval_value ?? 0;
        
        // 🔥 CEK PERUBAHAN INTERVAL
        if ($lastIntervalValue != $interval) {
            Log::info("🔄 INTERVAL BERUBAH: {$lastIntervalValue} → {$interval} menit - RESET TIMER (status awal TETAP)");
            
            $service->update([
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
                // ❌ JANGAN update last_interval_status! Status awal TETAP dari awal interval!
            ]);
            return;
        }
        
        // INISIALISASI PERTAMA KALI
        if (empty($lastIntervalCheck) || empty($lastIntervalStatus)) {
            Log::info("🔄 INTERVAL INIT: {$service->name} | Status awal: {$status}");
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            return;
        }

        // HITUNG SELISIH WAKTU (TIMER TETAP BERJALAN)
        $lastCheck = Carbon::parse($lastIntervalCheck);
        $minutesSinceLastCheck = $lastCheck->diffInRealMinutes(now());
        
        Log::info("⏱️ TIMER: {$minutesSinceLastCheck}/{$interval} menit | Status awal: {$lastIntervalStatus} | Status skrg: {$status}");
        
        // BELUM MENCAPAI INTERVAL → TIDAK KIRIM
        if ($minutesSinceLastCheck < $interval) {
            Log::info("⏳ Interval belum tercapai ({$minutesSinceLastCheck}/{$interval} menit) - TIDAK KIRIM WA");
            
            if ($oldStatus !== $status) {
                Log::info("📝 Status berubah di tengah interval: {$oldStatus} → {$status} (DIABAIKAN)");
            }
            return;
        }

        // ============================================================
        // INTERVAL TERCAPAI!
        // ============================================================
        Log::info("🎯 INTERVAL REACHED! {$service->name} | Awal: {$lastIntervalStatus} | Akhir: {$status}");
        
        if ($status !== $lastIntervalStatus) {
            // STATUS BERUBAH → KIRIM WA
            Log::info("✅ STATUS BERUBAH: {$lastIntervalStatus} → {$status} (KIRIM WA)");
            
            if ($status === 'UP') {
                $this->sendRestoredAlert($service, $lastIntervalStatus, $code, $time, $detail);
            } else {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            }
            
            $service->update([
                'last_wa_sent_at' => now(),
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
            Log::info("✅ WA terkirim: {$service->name} {$lastIntervalStatus} → {$status}");
        } else {
            // STATUS SAMA → TIDAK KIRIM
            Log::info("⏭️ Status tetap {$status} (sama dengan awal interval) - TIDAK KIRIM WA");
            
            $service->update([
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            Log::info("⏱️ Timer direset untuk interval berikutnya");
        }
    }

    /**
     * ============================================================
     * 🟢 KIRIM WA SERVICE NORMAL KEMBALI (RESTORED)
     * ============================================================
     */
    private function sendRestoredAlert($service, $oldStatus, $code, $time, $detail)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif untuk kirim WA restored alert');
            return;
        }

        $newline = "\n";
        $statusText = $oldStatus == 'DOWN' ? 'DOWN' : 'WARNING';

        $message = "🟢 SERVICE NORMAL KEMBALI" . $newline . $newline;
        $message .= "Nama   : " . $service->name . $newline;
        $message .= "Target : " . $service->target . $newline;
        $message .= $newline;
        $message .= "Status : 🟢 UP (sebelumnya " . $statusText . ")" . $newline;
        $message .= "Kode   : " . $code . $newline;
        $message .= "Waktu  : " . $time . " detik" . $newline;
        
        if (!empty($detail) && $detail != '-') {
            $message .= $newline . "Detail:" . $newline;
            $message .= $detail . $newline;
        }
        
        $message .= $newline . "✅ Service telah kembali normal dan dapat diakses." . $newline;
        $message .= $newline . "🕐 " . now()->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ WA RESTORED ke: {$contact->phone} - {$service->name}" : "❌ Gagal WA RESTORED ke: {$contact->phone}");
        }
    }

    /**
     * ============================================================
     * ⚠️ KIRIM WHATSAPP (DOWN / WARNING)
     * ============================================================
     */
    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif');
            return;
        }

        $newline = "\n";

        if ($status == 'DOWN') {
            $judul = "🔴 SERVICE DOWN";
            $statusIcon = "🔴";
            $statusText = "DOWN";
        } else {
            $judul = "🟡 SERVICE WARNING";
            $statusIcon = "🟡";
            $statusText = "WARNING";
        }

        $message = $judul . $newline . $newline;
        $message .= "Nama   : " . $service->name . $newline;
        $message .= "Target : " . $service->target . $newline;
        $message .= $newline;
        $message .= "Status : " . $statusIcon . " " . $statusText . $newline;
        $message .= "Kode   : " . $code . $newline;
        $message .= "Waktu  : " . $time . " detik" . $newline;
        
        if (!empty($detail) && $detail != '-') {
            $message .= $newline . "Detail:" . $newline;
            $message .= $detail . $newline;
        }
        
        if (!empty($action) && $action != '-' && $action != 'Service dalam kondisi baik, tidak perlu tindakan') {
            $message .= $newline . "Tindakan:" . $newline;
            $message .= $action . $newline;
        }
        
        $message .= $newline . "🕐 " . now()->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "WA ke: {$contact->phone} - {$status}" : "Gagal WA ke: {$contact->phone}");
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
            Log::info('Network: DISCONNECTED');
            $this->networkAlertSent = true;
        }
        if ($isNetworkConnected && $this->networkAlertSent) {
            Log::info('Network: RESTORED');
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