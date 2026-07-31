<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ServiceMonitorService
{
    private $networkAlertSent = false;
    private $consecutiveNetworkFailures = 0;
    private const MAX_NETWORK_FAILURES = 2;
    
    private $isNetworkConnected = true;
    private $lastNetworkCheckTime = null;

    /**
     * ============================================================
     * 🔍 CHECK SINGLE SERVICE - EARLY RETURN TOTAL
     * ============================================================
     */
    public function check(Service $service)
    {
        $isNetworkConnected = $this->checkNetworkConnection();
        
        Log::info('🔍 [check] Internet status for ' . $service->name . ': ' . ($isNetworkConnected ? 'ONLINE' : 'OFFLINE'));
        
        if (!$isNetworkConnected) {
            Log::info("⏸️ Internet DOWN - Skip check untuk {$service->name}, status TETAP: {$service->last_status}");
            return;
        }

        if ($service->type === 'ping') {
            return $this->checkPing($service);
        }
        return $this->checkHttp($service);
    }

    /**
     * ============================================================
     * 🌐 CEK KONEKSI INTERNET
     * ============================================================
     */
    public function checkNetworkConnection()
    {
        $cacheKey = 'network_connection_status';
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            $this->isNetworkConnected = $cached;
            $this->lastNetworkCheckTime = now();
            
            if (!$cached) {
                Cache::put('internet_down', true, 300);
                Log::info('🌐 [CACHE] Internet: OFFLINE');
            } else {
                Cache::forget('internet_down');
                Log::info('🌐 [CACHE] Internet: ONLINE');
            }
            
            return $cached;
        }

        Log::info('🔍 [CHECK] Starting internet connection check...');

        // METHOD 1: HTTP
        $httpTargets = [
            'https://www.google.com',
            'https://www.cloudflare.com',
            'https://www.microsoft.com',
            'https://www.amazon.com'
        ];
        
        foreach ($httpTargets as $target) {
            try {
                Log::info("🔍 [HTTP] Checking: {$target}");
                
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'follow_location' => 1,
                        'max_redirects' => 2,
                        'ignore_errors' => true,
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);
                
                $response = @file_get_contents($target, false, $context);
                
                if ($response !== false) {
                    Log::info("✅ [HTTP] SUCCESS: {$target}");
                    $this->consecutiveNetworkFailures = 0;
                    $this->isNetworkConnected = true;
                    $this->lastNetworkCheckTime = now();
                    Cache::put($cacheKey, true, 10);
                    Cache::forget('internet_down');
                    return true;
                }
            } catch (\Exception $e) {
                Log::info("❌ [HTTP] FAILED: {$target} - " . $e->getMessage());
            }
        }

        // METHOD 2: CURL
        if (function_exists('curl_init')) {
            $curlTargets = ['https://www.google.com', 'https://www.cloudflare.com'];
            
            foreach ($curlTargets as $target) {
                try {
                    Log::info("🔍 [CURL] Checking: {$target}");
                    
                    $ch = curl_init($target);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode > 0 && $httpCode < 500) {
                        Log::info("✅ [CURL] SUCCESS: {$target} - Code: {$httpCode}");
                        $this->consecutiveNetworkFailures = 0;
                        $this->isNetworkConnected = true;
                        $this->lastNetworkCheckTime = now();
                        Cache::put($cacheKey, true, 10);
                        Cache::forget('internet_down');
                        return true;
                    }
                } catch (\Exception $e) {
                    Log::info("❌ [CURL] FAILED: {$target}");
                }
            }
        }

        // METHOD 3: PING
        $pingTargets = ['8.8.8.8', '1.1.1.1'];
        
        foreach ($pingTargets as $target) {
            Log::info("🔍 [PING] Checking: {$target}");
            if ($this->pingHost($target)) {
                Log::info("✅ [PING] SUCCESS: {$target}");
                $this->consecutiveNetworkFailures = 0;
                $this->isNetworkConnected = true;
                $this->lastNetworkCheckTime = now();
                Cache::put($cacheKey, true, 10);
                Cache::forget('internet_down');
                return true;
            }
            Log::info("❌ [PING] FAILED: {$target}");
        }

        // METHOD 4: DNS
        $dnsTargets = ['google.com', 'cloudflare.com'];
        foreach ($dnsTargets as $target) {
            Log::info("🔍 [DNS] Checking: {$target}");
            if (checkdnsrr($target, 'A')) {
                Log::info("✅ [DNS] SUCCESS: {$target}");
                $this->consecutiveNetworkFailures = 0;
                $this->isNetworkConnected = true;
                $this->lastNetworkCheckTime = now();
                Cache::put($cacheKey, true, 10);
                Cache::forget('internet_down');
                return true;
            }
            Log::info("❌ [DNS] FAILED: {$target}");
        }

        Log::info('❌ [CHECK] ALL METHODS FAILED - Internet DOWN');
        $this->consecutiveNetworkFailures++;
        $this->isNetworkConnected = false;
        $this->lastNetworkCheckTime = now();
        Cache::put($cacheKey, false, 10);
        Cache::put('internet_down', true, 300);
        
        return false;
    }

    /**
     * ============================================================
     * 📡 PING HOST
     * ============================================================
     */
    private function pingHost($host)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            $command = "ping -n 1 -w 3000 " . escapeshellarg($host) . " 2>&1";
        } else {
            $command = "ping -c 1 -W 3 " . escapeshellarg($host) . " 2>&1";
        }
        
        exec($command, $output, $resultCode);
        
        if ($resultCode === 0) {
            return true;
        }
        
        $outputString = implode("\n", $output);
        
        if (strpos($outputString, 'Destination host unreachable') !== false ||
            strpos($outputString, 'Request timed out') !== false ||
            strpos($outputString, 'timed out') !== false ||
            strpos($outputString, 'unreachable') !== false) {
            return false;
        }
        
        return false;
    }

    /**
     * ============================================================
     * 📊 GET NETWORK STATUS
     * ============================================================
     */
    public function getNetworkStatus()
    {
        return [
            'connected' => $this->isNetworkConnected,
            'failures' => $this->consecutiveNetworkFailures,
            'max_failures' => self::MAX_NETWORK_FAILURES,
            'last_check' => $this->lastNetworkCheckTime ? $this->lastNetworkCheckTime->toDateTimeString() : null,
            'alert_sent' => $this->networkAlertSent,
            'status' => $this->isNetworkConnected ? 'ONLINE' : 'OFFLINE',
            'message' => $this->isNetworkConnected 
                ? '🌐 Internet connection is available' 
                : '🌐 No internet connection - Monitoring paused'
        ];
    }

    /**
     * ============================================================
     * 🔄 RESET NETWORK STATUS
     * ============================================================
     */
    public function resetNetworkStatus()
    {
        $this->consecutiveNetworkFailures = 0;
        $this->isNetworkConnected = true;
        $this->networkAlertSent = false;
        Cache::forget('network_connection_status');
        Cache::forget('internet_down');
        Log::info('🌐 Network status has been reset');
        return true;
    }

    /**
     * ============================================================
     * 📊 GET INTERNET STATUS
     * ============================================================
     */
    public function getInternetStatus()
    {
        $connected = $this->checkNetworkConnection();
        
        return [
            'connected' => $connected,
            'status' => $connected ? 'ONLINE' : 'OFFLINE',
            'message' => $connected 
                ? '🌐 Internet connection is available' 
                : '🌐 No internet connection - Monitoring paused',
            'timestamp' => now()->timestamp,
            'checked_at' => now()->toDateTimeString()
        ];
    }

    /**
     * ============================================================
     * 🔍 CHECK HTTP - FIXED REDIRECT HANDLING
     * ============================================================
     */
    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status;
        $code = null;
        $time = 0;
        $start = microtime(true);

        try {
            $url = $this->normalizeUrl($service->target);
            $start = microtime(true);

            // 🔥 FIX: Hapus withoutRedirecting() - biarkan HTTP client mengikuti redirect
            $response = Http::timeout(20)
                ->connectTimeout(12)
                ->get($url);  // ← withoutRedirecting() dihapus

            $time = round(microtime(true) - $start, 2);
            $code = $response->status();
            
            // Ambil URL akhir setelah redirect (jika ada)
            $effectiveUrl = $url;
            try {
                // Coba dapatkan URL efektif
                $effectiveUrl = (string) $response->effectiveUri();
            } catch (\Exception $e) {
                // Jika tidak bisa mendapatkan effectiveUri, gunakan URL asli
                $effectiveUrl = $url;
            }

            Log::info("HTTP Response {$service->name}: code={$code}, time={$time}s, effective_url={$effectiveUrl}");

            // 🔥 FIX: Deteksi redirect dan log untuk info
            if ($code >= 300 && $code < 400) {
                $redirectLocation = $response->header('Location');
                Log::info("🔀 REDIRECT DETECTED: {$service->name} → {$url} → {$redirectLocation} (Code: {$code})");
                
                // Cek apakah URL asli berbeda dengan URL akhir
                if ($url !== $effectiveUrl) {
                    Log::info("📍 URL AKHIR: {$effectiveUrl} (berbeda dari asli: {$url})");
                }
            }

            // FIXED: Pass $time to analyzeResponseByCode
            $analysis = $this->analyzeResponseByCode($code, $response->body(), $time, $effectiveUrl);
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

        $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                         $analysis['reason'], $analysis['detail'], $analysis['action']);
    }

    /**
     * ============================================================
     * 📡 CHECK PING - LENGKAP
     * ============================================================
     */
    private function checkPing(Service $service)
    {
        $oldStatus = $service->last_status;
        $code = 'N/A';
        $time = 0;
        $start = microtime(true);

        $target = $service->target;
        $parts = explode(':', $target);
        $host = $parts[0];
        $port = isset($parts[1]) ? (int)$parts[1] : null;

        if ($port) {
            if ($port < 1 || $port > 65535) {
                $time = round(microtime(true) - $start, 2);
                $code = 'INVALID_PORT';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'INVALID_PORT', "Port {$port} tidak valid", 'Periksa format port (1-65535)');
                return;
            }

            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            $time = round(microtime(true) - $start, 2);

            if ($connection) {
                fclose($connection);
                $code = 'PORT_OPEN';
                $this->saveResult($service, $oldStatus, 'UP', $code, $time, 
                                 'PORT_OK', "Host {$host} merespon port {$port}", 'Port terbuka, service berjalan normal');
            } else {
                $code = 'PORT_CLOSED';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'PORT_CLOSED', "Port {$port} tidak merespon", 'Periksa firewall dan pastikan service berjalan di port tersebut');
            }
            return;
        }

        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            if (!checkdnsrr($host, 'A') && !checkdnsrr($host, 'AAAA')) {
                $time = round(microtime(true) - $start, 2);
                $code = 'DNS_ERROR';
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'DNS_ERROR', "Hostname {$host} tidak dapat di-resolve", 'Periksa konfigurasi DNS server');
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

        // ============================================================
        // 🔥 ANALISIS PING - LENGKAP!
        // ============================================================
        
        // 1. UNREACHABLE
        if (strpos($outputString, 'Destination host unreachable') !== false ||
            strpos($outputString, 'Host unreachable') !== false ||
            strpos($outputString, 'unreachable') !== false) {
            $code = 'UNREACHABLE';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'HOST_UNREACHABLE', 'Host tidak dapat dijangkau', 
                             'Periksa koneksi jaringan, firewall, dan routing');
            Log::warning("Host UNREACHABLE: {$host}");
            return;
        }

        // 2. NETWORK UNREACHABLE
        if (strpos($outputString, 'Network is unreachable') !== false ||
            strpos($outputString, 'network unreachable') !== false) {
            $code = 'NETWORK_UNREACHABLE';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'NETWORK_UNREACHABLE', 'Jaringan tidak dapat menjangkau host', 
                             'Periksa routing dan konfigurasi firewall');
            Log::warning("NETWORK UNREACHABLE: {$host}");
            return;
        }

        // 3. TIMEOUT
        if (strpos($outputString, 'Request timed out') !== false ||
            strpos($outputString, 'timeout') !== false ||
            strpos($outputString, 'Timed out') !== false) {
            
            preg_match('/(\d+)\s*received/i', $outputString, $receivedMatches);
            $received = isset($receivedMatches[1]) ? intval($receivedMatches[1]) : 0;
            
            if ($received > 0) {
                $code = 'PING_PARTIAL';
                $this->saveResult($service, $oldStatus, 'WARNING', $code, $time, 
                                 'PING_PARTIAL', "Ping timeout ({$received}/2 berhasil) - Host merespon lambat", 
                                 'Packet loss terdeteksi, periksa kualitas jaringan');
                Log::warning("PING PARTIAL: {$host} - {$received}/2 berhasil");
                return;
            }
            
            $code = 'TIMEOUT';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'PING_TIMEOUT', 'Request timeout - Host tidak merespon', 
                             'Periksa firewall dan pastikan host menyala');
            Log::warning("PING TIMEOUT: {$host}");
            return;
        }

        // 4. TTL EXPIRED
        if (strpos($outputString, 'TTL expired') !== false ||
            strpos($outputString, 'TTL Exceeded') !== false) {
            $code = 'TTL_EXPIRED';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'TTL_EXPIRED', 'TTL expired - Hop terlalu jauh', 
                             'Periksa routing jaringan, mungkin ada loop atau hop terlalu banyak');
            Log::warning("TTL EXPIRED: {$host}");
            return;
        }

        // 5. GENERAL FAILURE
        if (strpos($outputString, 'General failure') !== false) {
            $code = 'GENERAL_FAILURE';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'GENERAL_FAILURE', 'General failure - Masalah jaringan lokal', 
                             'Periksa adapter jaringan dan konfigurasi firewall');
            Log::warning("GENERAL FAILURE: {$host}");
            return;
        }

        // 6. DESTINATION NET UNREACHABLE
        if (strpos($outputString, 'Destination net unreachable') !== false) {
            $code = 'NET_UNREACHABLE';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'NET_UNREACHABLE', 'Destination net unreachable', 
                             'Periksa routing dan konfigurasi firewall');
            Log::warning("DESTINATION NET UNREACHABLE: {$host}");
            return;
        }

        // 7. DESTINATION PORT UNREACHABLE
        if (strpos($outputString, 'Destination port unreachable') !== false) {
            $code = 'PORT_UNREACHABLE';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'PORT_UNREACHABLE', 'Destination port unreachable', 
                             'Periksa firewall dan service di port tersebut');
            Log::warning("DESTINATION PORT UNREACHABLE: {$host}");
            return;
        }

        // 8. PACKET LOSS
        preg_match('/(\d+)%\s*loss/i', $outputString, $lossMatches);
        if (isset($lossMatches[1])) {
            $loss = intval($lossMatches[1]);
            if ($loss >= 50) {
                $code = 'HIGH_PACKET_LOSS';
                $this->saveResult($service, $oldStatus, 'WARNING', $code, $time, 
                                 'HIGH_PACKET_LOSS', "Packet loss {$loss}% - Koneksi tidak stabil", 
                                 'Kualitas jaringan buruk, periksa kabel/switch/router');
                Log::warning("HIGH PACKET LOSS: {$host} - {$loss}%");
                return;
            }
            
            if ($loss > 0 && $loss < 50) {
                $code = 'PACKET_LOSS';
                $this->saveResult($service, $oldStatus, 'WARNING', $code, $time, 
                                 'PACKET_LOSS', "Packet loss {$loss}% - Koneksi kurang stabil", 
                                 'Periksa kualitas jaringan, mungkin ada interferensi');
                Log::warning("PACKET LOSS: {$host} - {$loss}%");
                return;
            }
        }

        // 9. DNS ERROR
        if (strpos($outputString, 'could not find host') !== false ||
            strpos($outputString, 'Unknown host') !== false) {
            $code = 'DNS_ERROR';
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'DNS_ERROR', "Hostname {$host} tidak dapat di-resolve", 
                             'Periksa konfigurasi DNS server');
            Log::warning("DNS ERROR: {$host}");
            return;
        }

        // 10. PING OK
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
                $this->saveResult($service, $oldStatus, 'WARNING', $code, 
                                 $avgTime > 0 ? $avgTime : $time, 
                                 'PING_OK_SLOW', 
                                 "Host merespon tapi lambat (avg: {$avgTime}s, min: {$minTime}s, max: {$maxTime}s)", 
                                 'Response lambat, optimasi jaringan atau server');
                Log::info("PING OK (SLOW): {$host} - avg: {$avgTime}s");
            } else {
                $this->saveResult($service, $oldStatus, 'UP', $code, 
                                 $avgTime > 0 ? $avgTime : $time, 
                                 'PING_OK', 
                                 "Host merespon ping (avg: {$avgTime}s, min: {$minTime}s, max: {$maxTime}s)", 
                                 'Service dalam kondisi baik, tidak perlu tindakan');
                Log::info("PING OK: {$host} - avg: {$avgTime}s");
            }
            return;
        }

        // 11. PING FAILED
        $code = 'PING_FAILED';
        $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                         'PING_FAILED', 'Host tidak merespon ping (unknown reason)', 
                         'Periksa koneksi jaringan dan konfigurasi firewall');
        Log::warning("PING FAILED (unknown): {$host}");
    }

    /**
     * ============================================================
     * 📊 ANALISIS RESPONSE - FIXED REDIRECT 301
     * ============================================================
     */
    private function analyzeResponseByCode($code, $body, $time, $effectiveUrl = null)
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

        // FIXED: Pass $time and $effectiveUrl to analyzeResponse
        return $this->analyzeResponse($code, $time, $effectiveUrl);
    }

    /**
     * ============================================================
     * 📊 ANALYZE RESPONSE - FIXED REDIRECT 301
     * ============================================================
     */
    private function analyzeResponse($code, $time, $effectiveUrl = null)
    {
        // ============================================================
        // 🔥 FIX: REDIRECT 301 & 308 - Status UP dengan informasi tambahan
        // ============================================================
        if ($code >= 300 && $code < 400) {
            $redirectCodes = [
                300 => 'Multiple Choices',
                301 => 'Permanent Redirect',
                302 => 'Temporary Redirect',
                303 => 'See Other',
                307 => 'Temporary Redirect',
                308 => 'Permanent Redirect'
            ];

            $actionMessages = [
                300 => '🔧 Periksa pilihan resource yang tersedia',
                301 => '🔧 Update URL endpoint ke target redirect permanen',
                302 => '🔧 Cek apakah redirect sementara masih diperlukan',
                303 => '🔧 Periksa konfigurasi redirect (method POST → GET)',
                307 => '🔧 Cek apakah redirect sementara masih diperlukan',
                308 => '🔧 Update URL endpoint ke target redirect permanen'
            ];

            $codeName = $redirectCodes[$code] ?? 'Redirect';
            $detailMessage = "{$codeName} - Service masih bisa diakses";
            
            // Tambahkan informasi URL efektif jika ada
            if ($effectiveUrl && $effectiveUrl !== '') {
                $detailMessage .= " | Redirect ke: {$effectiveUrl}";
            }

            return [
                'status' => 'UP',
                'reason' => 'HTTP_' . $code,
                'detail' => $detailMessage . ' - Pengguna tetap bisa akses',
                'action' => $actionMessages[$code] ?? 'Periksa konfigurasi redirect jika mengganggu akses'
            ];
        }

        // ============================================================
        // 200 SUCCESS
        // ============================================================
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

        // ============================================================
        // 400 CLIENT ERRORS
        // ============================================================
        if ($code >= 400 && $code < 500) {
            $clientErrors = [
                400 => ['status' => 'WARNING', 'reason' => 'HTTP_400', 'detail' => 'Bad Request - Pengguna bisa akses dengan perbaikan', 'action' => 'Periksa format request yang dikirim'],
                401 => ['status' => 'UP', 'reason' => 'HTTP_401', 'detail' => 'Unauthorized - Pengguna perlu login - Masih bisa akses', 'action' => 'Pastikan kredensial login benar'],
                403 => ['status' => 'UP', 'reason' => 'HTTP_403', 'detail' => 'Forbidden - Pengguna perlu izin - Masih bisa akses', 'action' => 'Periksa izin akses pengguna'],
                404 => ['status' => 'DOWN', 'reason' => 'HTTP_404', 'detail' => 'Halaman tidak ditemukan - Pengguna tidak bisa akses', 'action' => 'Periksa URL endpoint, mungkin sudah berubah'],
                405 => ['status' => 'WARNING', 'reason' => 'HTTP_405', 'detail' => 'Method HTTP tidak diizinkan', 'action' => 'Ganti method HTTP yang digunakan'],
                408 => ['status' => 'DOWN', 'reason' => 'HTTP_408', 'detail' => 'Request Timeout - Pengguna tidak bisa akses', 'action' => 'Cek performa server, mungkin overload'],
                410 => ['status' => 'DOWN', 'reason' => 'HTTP_410', 'detail' => 'Gone - Resource sudah tidak tersedia', 'action' => 'Update URL atau hapus monitoring jika sudah tidak digunakan'],
                415 => ['status' => 'DOWN', 'reason' => 'HTTP_415', 'detail' => 'Unsupported Media Type', 'action' => 'Periksa header Content-Type yang dikirim'],
                429 => ['status' => 'WARNING', 'reason' => 'HTTP_429', 'detail' => 'Too Many Requests - Rate limit', 'action' => 'Kurangi frekuensi request, tunggu beberapa saat'],
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

        // ============================================================
        // 500 SERVER ERRORS
        // ============================================================
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
     * 💾 SAVE RESULT
     * ============================================================
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        if (!$this->checkNetworkConnection()) {
            Log::warning("⛔ Internet DOWN - Tidak menyimpan hasil untuk {$service->name}");
            return;
        }

        if ($code === null || $code === '') {
            $code = 'N/A';
        }

        $statusChanged = ($oldStatus !== $status);
        $oldCode = $service->last_code ?? 'N/A';
        $codeChanged = ($oldCode != $code);
        $isWarning = ($status === 'WARNING');
        $shouldSaveLog = $statusChanged || $codeChanged || $isWarning;
        
        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail,
            'last_check_at' => now(),
        ]);

        if ($shouldSaveLog) {
            $isFirstCheck = empty($oldStatus) || $oldStatus === 'UNKNOWN' || empty($service->last_wa_sent_at);

            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $status,
                'response_code' => $code,
                'response_time' => $time,
                'message' => $detail,
                'action' => $action,
                'checked_at' => now(),
                'is_status_change' => $statusChanged,
                'previous_status' => $oldStatus,
            ]);

            if ($statusChanged) {
                Log::info("🔄 STATUS BERUBAH: {$service->name} {$oldStatus} → {$status}, Code: {$code}");
            } elseif ($codeChanged) {
                Log::info("🔄 CODE BERUBAH: {$service->name} {$oldCode} → {$code}, Status: {$status}");
            } else {
                Log::info("⚠️ WARNING: {$service->name} - {$detail}");
            }
        } else {
            Log::info("⏭️ NO CHANGE: {$service->name} tetap {$status} (Code: {$code})");
        }

        $isFirstCheck = empty($oldStatus) || $oldStatus === 'UNKNOWN' || empty($service->last_wa_sent_at);
        $this->handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck);
    }

    /**
     * ============================================================
     * 🔄 HANDLE INTERVAL LOGIC
     * ============================================================
     */
    private function handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck = false)
    {
        $interval = $service->wa_interval_minutes ?? 0;
        
        Log::info("🔍 INTERVAL CHECK: {$service->name} | Interval: {$interval} menit | Status: {$status} | Old: {$oldStatus}");

        // FIRST CHECK: DOWN/WARNING → LANGSUNG KIRIM
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
        
        // FIRST CHECK: UP → TIDAK KIRIM
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

        // INTERVAL > 0
        $lastIntervalCheck = $service->last_interval_checked_at;
        $lastIntervalStatus = $service->last_interval_status;
        $lastIntervalValue = $service->last_interval_value ?? 0;
        
        // CEK PERUBAHAN INTERVAL
        if ($lastIntervalValue != $interval) {
            Log::info("🔄 INTERVAL BERUBAH: {$lastIntervalValue} → {$interval} menit - RESET TIMER");
            
            $service->update([
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
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

        // HITUNG SELISIH WAKTU
        $lastCheck = Carbon::parse($lastIntervalCheck);
        $minutesSinceLastCheck = $lastCheck->diffInRealMinutes(now());
        
        Log::info("⏱️ TIMER: {$minutesSinceLastCheck}/{$interval} menit | Status awal: {$lastIntervalStatus} | Status skrg: {$status}");
        
        // BELUM MENCAPAI INTERVAL
        if ($minutesSinceLastCheck < $interval) {
            Log::info("⏳ Interval belum tercapai ({$minutesSinceLastCheck}/{$interval} menit) - TIDAK KIRIM WA");
            return;
        }

        // INTERVAL TERCAPAI!
        Log::info("🎯 INTERVAL REACHED! {$service->name} | Awal: {$lastIntervalStatus} | Akhir: {$status}");
        
        if ($status !== $lastIntervalStatus) {
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
            Log::info("⏭️ Status tetap {$status} - TIDAK KIRIM WA");
            
            $service->update([
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
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
            Log::info($result ? "✅ WA ke: {$contact->phone} - {$status}" : "❌ Gagal WA ke: {$contact->phone}");
        }
    }

    /**
     * ============================================================
     * 🔧 HELPER METHODS
     * ============================================================
     */
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
            Log::info('🌐 Network: DISCONNECTED (status preserved, no WA sent)');
            $this->networkAlertSent = true;
        }
        if ($isNetworkConnected && $this->networkAlertSent) {
            Log::info('🌐 Network: RESTORED - resuming normal checks');
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