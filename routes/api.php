<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmokeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC API ====================

Route::post('/smoke', [SmokeController::class, 'receiveData'])
    ->middleware('throttle:60,1');

Route::get('/smoke/status', [SmokeController::class, 'getStatus']);
Route::get('/smoke/logs', [SmokeController::class, 'getLogs']);

Route::post('/login', [LoginController::class, 'apiLogin']);

// 🔥 PERBAIKI: CEK JARINGAN LEBIH AKURAT
Route::get('/network/status', function () {
    $connected = false;
    
    // 🔥 CEK VIA HTTP REQUEST (LEBIH AKURAT)
    try {
        $response = Http::timeout(3)->get('https://www.google.com');
        $connected = $response->successful();
    } catch (\Exception $e) {
        $connected = false;
    }
    
    // 🔥 JIKA HTTP GAGAL, CEK VIA PING
    if (!$connected) {
        $targets = ['8.8.8.8', '1.1.1.1', 'google.com'];
        foreach ($targets as $target) {
            exec("ping -n 1 " . escapeshellarg($target), $output, $status);
            if ($status === 0) {
                $connected = true;
                break;
            }
        }
    }
    
    return response()->json([
        'success' => true,
        'connected' => $connected,
        'timestamp' => now()->toDateTimeString()
    ]);
});

Route::get('/test-api', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Laravel berjalan',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// ==================== PROTECTED API ====================

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [LoginController::class, 'apiLogout']);
    Route::get('/user', function () {
        return response()->json(auth()->user());
    });

    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);
    Route::get('/dashboard/uptime', [DashboardController::class, 'apiUptime']);
    Route::get('/dashboard/charts', [DashboardController::class, 'apiCharts']);
    Route::get('/dashboard/recent-issues', [DashboardController::class, 'apiRecentIssues']);
    Route::get('/dashboard/service-health', [DashboardController::class, 'apiServiceHealth']);

    Route::get('/services', [ServiceController::class, 'apiIndex']);
    Route::get('/services/{id}', [ServiceController::class, 'apiShow']);
    Route::post('/services', [ServiceController::class, 'apiStore']);
    Route::put('/services/{id}', [ServiceController::class, 'apiUpdate']);
    Route::delete('/services/{id}', [ServiceController::class, 'apiDestroy']);

    Route::get('/services/{id}/detail', [ServiceController::class, 'detail']);
    Route::get('/services/{id}/logs', [ServiceController::class, 'logs']);
    Route::post('/services/{id}/check', [ServiceController::class, 'check']);
    Route::get('/services/overview', [ServiceController::class, 'overview']);
    Route::get('/services/{id}/health', [ServiceController::class, 'health']);
    Route::get('/services/{id}/download-report', [ServiceController::class, 'downloadReport']);

    Route::get('/contacts', [ContactController::class, 'apiIndex']);
    Route::get('/contacts/{id}', [ContactController::class, 'apiShow']);
    Route::post('/contacts', [ContactController::class, 'apiStore']);
    Route::put('/contacts/{id}', [ContactController::class, 'apiUpdate']);
    Route::delete('/contacts/{id}', [ContactController::class, 'apiDestroy']);

    Route::get('/logs', [LogController::class, 'apiIndex']);
    Route::get('/logs/service', [LogController::class, 'apiServiceLogs']);
    Route::get('/logs/smoke', [LogController::class, 'apiSmokeLogs']);
    Route::get('/logs/service/{id}', [LogController::class, 'apiServiceLogsById']);
    Route::get('/logs/summary', [LogController::class, 'apiSummary']);
    Route::get('/logs/stats', [LogController::class, 'apiStats']);

});