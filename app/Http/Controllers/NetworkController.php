<?php

namespace App\Http\Controllers;

use App\Services\ServiceMonitorService;
use Illuminate\Http\Request;

class NetworkController extends Controller
{
    protected $monitorService;

    public function __construct(ServiceMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    public function status(Request $request)
    {
        try {
            // Gunakan method yang sudah ada di ServiceMonitorService
            $isConnected = $this->monitorService->checkNetworkConnection();
            
            return response()->json([
                'success' => true,
                'connected' => $isConnected,
                'timestamp' => now()->toIso8601String(),
                'checked_at' => now()->format('d-m-Y H:i:s')
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
}