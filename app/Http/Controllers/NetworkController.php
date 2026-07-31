<?php

namespace App\Http\Controllers;

use App\Services\ServiceMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetworkController extends Controller
{
    protected $monitorService;

    public function __construct(ServiceMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    /**
     * ============================================================
     *  📡 API: CEK STATUS JARINGAN
     *  ============================================================
     *  🔗 URL: GET /api/network/status
     *  🔑 PUBLIK - TANPA AUTH
     * ============================================================
     */
    public function status(Request $request)
    {
        try {
            // Gunakan multiple method untuk cek koneksi
            $connectionStatus = $this->checkInternetConnection();
            
            return response()->json([
                'success' => true,
                'connected' => $connectionStatus,
                'timestamp' => now()->toIso8601String(),
                'checked_at' => now()->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Network status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'connected' => false,
                'error' => 'Failed to check network: ' . $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 200);
        }
    }

    /**
     * Cek koneksi internet dengan multiple method
     */
    private function checkInternetConnection()
    {
        // Method 1: HTTP Request ke Google
        try {
            $response = Http::timeout(3)
                ->withOptions(['verify' => false])
                ->get('https://www.google.com');
            
            if ($response->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            Log::debug('HTTP Google failed: ' . $e->getMessage());
        }

        // Method 2: HTTP Request ke Cloudflare
        try {
            $response = Http::timeout(3)
                ->withOptions(['verify' => false])
                ->get('https://cloudflare.com');
            
            if ($response->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            Log::debug('HTTP Cloudflare failed: ' . $e->getMessage());
        }

        // Method 3: DNS Resolution
        try {
            $ip = gethostbyname('google.com');
            if ($ip !== 'google.com' && filter_var($ip, FILTER_VALIDATE_IP)) {
                return true;
            }
        } catch (\Exception $e) {
            Log::debug('DNS check failed: ' . $e->getMessage());
        }

        // Method 4: Ping
        try {
            if ($this->pingTarget('8.8.8.8') || $this->pingTarget('1.1.1.1')) {
                return true;
            }
        } catch (\Exception $e) {
            Log::debug('Ping check failed: ' . $e->getMessage());
        }

        // Jika semua gagal, return false
        return false;
    }

    /**
     * Ping target dengan timeout
     */
    private function pingTarget($target)
    {
        $target = escapeshellarg($target);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: timeout 1 detik
            exec("ping -n 1 -w 1000 {$target} 2>NUL", $output, $status);
        } else {
            // Linux/Mac: timeout 1 detik
            exec("ping -c 1 -W 1 {$target} 2>/dev/null", $output, $status);
        }
        
        return $status === 0;
    }

    /**
     * ============================================================
     *  🧪 TEST ENDPOINT - Untuk Debugging
     *  ============================================================
     */
    public function test(Request $request)
    {
        try {
            $results = [
                'http_google' => $this->testHttp('https://www.google.com'),
                'http_cloudflare' => $this->testHttp('https://cloudflare.com'),
                'dns' => $this->testDns('google.com'),
                'ping_8.8.8.8' => $this->testPing('8.8.8.8'),
                'ping_1.1.1.1' => $this->testPing('1.1.1.1'),
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'os' => PHP_OS,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                    'disable_functions' => ini_get('disable_functions'),
                    'allow_url_fopen' => ini_get('allow_url_fopen')
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $results,
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function testHttp($url)
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(3)->withOptions(['verify' => false])->get($url);
            $time = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'time' => $time . 'ms'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function testDns($domain)
    {
        try {
            $start = microtime(true);
            $ip = gethostbyname($domain);
            $time = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'success' => $ip !== $domain,
                'ip' => $ip,
                'time' => $time . 'ms'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function testPing($target)
    {
        try {
            $start = microtime(true);
            $result = $this->pingTarget($target);
            $time = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'success' => $result,
                'time' => $time . 'ms'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}