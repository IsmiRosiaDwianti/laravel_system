<?php

namespace App\Http\Controllers;

use App\Services\ServiceMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
            $connected = $this->checkInternetConnection();
            
            return response()->json([
                'success' => true,
                'connected' => $connected,
                'timestamp' => now()->toIso8601String(),
                'checked_at' => now()->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 500);
        }
    }

    /**
     * Cek koneksi internet
     */
    private function checkInternetConnection()
    {
        // Coba akses Google via HTTP
        try {
            $response = Http::timeout(5)->get('https://www.google.com');
            return $response->successful();
        } catch (\Exception $e) {
            // Jika HTTP gagal, coba ping
            return $this->pingTargets();
        }
    }

    /**
     * Ping beberapa target
     */
    private function pingTargets()
    {
        $targets = ['8.8.8.8', '1.1.1.1', 'google.com'];
        
        foreach ($targets as $target) {
            if ($this->ping($target)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Ping satu target
     */
    private function ping($target)
    {
        // Untuk Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("ping -n 1 " . escapeshellarg($target), $output, $status);
        } else {
            // Untuk Linux/Mac
            exec("ping -c 1 " . escapeshellarg($target), $output, $status);
        }
        
        return $status === 0;
    }
}