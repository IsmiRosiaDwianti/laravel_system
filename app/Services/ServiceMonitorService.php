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

    /**
     * ============================================================
     * CHECK PING - TIMEOUT 5 DETIK
     * ============================================================
     */
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

        // PORT CHECK
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

        // CEK DNS
        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            if (!checkdnsrr($host, 'A') && !checkdnsrr($host, 'AAAA')) {
                $time = round(microtime(true) - $start, 2);
                $code = 'DNS_ERROR';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 'DNS_ERROR', "Hostname {$host} tidak dapat di-resolve", 'Periksa konfigurasi DNS server');
                return;
            }
        }

        // EKSEKUSI PING
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

        // ANALISIS HASIL PING

        // 1. Destination Host Unreachable
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

        // 2. Network is Unreachable
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

        // 3. Request Timed Out
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

        // 4. TTL Expired
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

        // 5. General Failure (Windows)
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

        // 6. Destination Net Unreachable
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

        // 7. Destination Port Unreachable
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

        // 8. Packet Loss
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

        // 9. Unknown Host / DNS Error
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

        // 10. SUCCESS
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

        // 11. DEFAULT
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
     * SAVE RESULT
     * ============================================================
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        if ($code === null || $code === '') {
            Log::warning("Code is null/empty for service {$service->name}, setting to 'N/A'");
            $code = 'N/A';
        }

        // UPDATE DATA SERVICE
        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail,
            'last_check_at' => now(),
        ]);

        // CEK APAKAH STATUS BERUBAH
        $statusChanged = ($oldStatus != $status);

        if ($statusChanged) {
            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $status,
                'response_code' => $code,
                'response_time' => $time,
                'message' => $detail,
                'action' => $action,
                'checked_at' => now(),
            ]);
            Log::info("LOG BARU: {$service->name} {$oldStatus} → {$status}, Code: {$code}");
        } else {
            $lastLog = ServiceLog::where('service_id', $service->id)
                ->latest()
                ->first();
            
            if ($lastLog) {
                $oldCode = $lastLog->response_code;
                $lastLog->update([
                    'response_code' => $code,
                    'response_time' => $time,
                    'message' => $detail,
                    'action' => $action,
                    'checked_at' => now(),
                ]);
                Log::info("LOG DIUPDATE: {$service->name} status tetap {$status}, code: {$oldCode} → {$code}");
            } else {
                ServiceLog::create([
                    'service_id' => $service->id,
                    'status' => $status,
                    'response_code' => $code,
                    'response_time' => $time,
                    'message' => $detail,
                    'action' => $action,
                    'checked_at' => now(),
                ]);
                Log::info("LOG BARU (force): {$service->name} {$status}, Code: {$code}");
            }
        }

        // FIRST CHECK
        $isFirstCheck = empty($service->last_wa_sent_at);
        
        if ($isFirstCheck) {
            Log::info("FIRST CHECK: {$service->name}");
            
            if ($status === 'DOWN' || $status === 'WARNING') {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => $status,
                    'last_interval_status' => $status,
                    'last_interval_checked_at' => now(),
                ]);
                Log::info("First check WA: {$service->name} → {$status}");
            } else {
                Log::info("First check UP: {$service->name} - Tidak perlu notifikasi");
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => 'UP',
                    'last_interval_status' => 'UP',
                    'last_interval_checked_at' => now(),
                ]);
            }
            
            return;
        }

        // WHATSAPP INTERVAL
        $interval = $service->wa_interval_minutes ?? 0;
        
        if ($interval == 0) {
            if ($status === 'DOWN' || $status === 'WARNING') {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => $status,
                ]);
                Log::info("WA terkirim (interval 0): {$service->name} → {$status}");
            }
            return;
        }

        $lastIntervalCheck = $service->last_interval_checked_at;
        
        if (empty($lastIntervalCheck)) {
            Log::info("Interval pertama: {$service->name}, status awal: {$status}");
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
            ]);
            
            if ($status === 'DOWN' || $status === 'WARNING') {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                $service->update([
                    'last_wa_sent_at' => now(),
                    'last_notified_status' => $status,
                ]);
                Log::info("WA terkirim (awal interval): {$service->name} → {$status}");
            }
            return;
        }

        $lastCheck = Carbon::parse($lastIntervalCheck);
        $minutesSinceLastCheck = $lastCheck->diffInRealMinutes(now());
        
        if ($minutesSinceLastCheck < $interval) {
            Log::info("Interval belum tercapai ({$minutesSinceLastCheck}/{$interval} menit), status: {$status}");
            return;
        }

        $intervalStartStatus = $service->last_interval_status ?? $oldStatus;
        
        Log::info("Interval reached! Start: {$intervalStartStatus}, Current: {$status}");
        
        if ($status !== $intervalStartStatus) {
            Log::info("WA terkirim: {$intervalStartStatus} → {$status} (interval {$interval} menit)");
            $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            $service->update([
                'last_wa_sent_at' => now(),
                'last_notified_status' => $status,
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
            ]);
        } else {
            Log::info("Skip WA: status tetap {$status} (sama dengan awal interval)");
            $service->update([
                'last_interval_checked_at' => now(),
            ]);
        }
    }

    /**
     * ============================================================
     * KIRIM WHATSAPP - FORMAT RAPIH & JELAS
     * ============================================================
     */
    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif');
            return;
        }

        // LINE PEMISAH
        $separator = "────────────────────";
        $bold = "*";
        $newline = "\n";

        // STATUS DENGAN JUDUL YANG JELAS
        if ($status == 'DOWN') {
            $judul = "🔴 SERVICE DOWN";
            $statusIcon = "🔴";
            $statusText = "DOWN";
        } elseif ($status == 'WARNING') {
            $judul = "🟡 SERVICE WARNING";
            $statusIcon = "🟡";
            $statusText = "WARNING";
        } else {
            $judul = "🟢 SERVICE NORMAL";
            $statusIcon = "🟢";
            $statusText = "UP";
        }

        // FORMAT PESAN
        $message = $separator . $newline;
        $message .= $bold . $judul . $bold . $newline;
        $message .= $separator . $newline . $newline;
        
        $message .= $bold . "Nama" . $bold . "   : " . $service->name . $newline;
        $message .= $bold . "Target" . $bold . " : " . $service->target . $newline;
        $message .= $separator . $newline;
        
        $message .= $bold . "Status" . $bold . " : " . $statusIcon . " " . $statusText . $newline;
        $message .= $bold . "Kode" . $bold . "   : " . $code . $newline;
        $message .= $bold . "Waktu" . $bold . "  : " . $time . " detik" . $newline;
        
        if (!empty($detail) && $detail != '-') {
            $message .= $separator . $newline;
            $message .= $bold . "📝 Detail" . $bold . $newline;
            $message .= $detail . $newline;
        }
        
        if (!empty($action) && $action != '-' && $action != 'Service dalam kondisi baik, tidak perlu tindakan') {
            $message .= $separator . $newline;
            $message .= $bold . "🔧 Tindakan" . $bold . $newline;
            $message .= $action . $newline;
        }
        
        $message .= $separator . $newline;
        $message .= "🕐 " . now()->format('d-m-Y H:i:s') . " WIB" . $newline;
        $message .= $separator;

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