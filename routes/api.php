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

// ==================== PUBLIC API (TANPA AUTH) ====================

Route::withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
    ->group(function () {
        
        Route::post('/smoke', [SmokeController::class, 'receiveData'])
            ->middleware('throttle:60,1');

        Route::get('/smoke/status', [SmokeController::class, 'getStatus']);
        Route::get('/smoke/logs', [SmokeController::class, 'getLogs']);

        Route::get('/test-api', function () {
            return response()->json([
                'success' => true,
                'message' => 'API Laravel berjalan',
                'timestamp' => now()->toDateTimeString()
            ]);
        });

    });

// ==================== AUTH ROUTES ====================

// SESSION-BASED AUTH
Route::middleware(['web'])->group(function () {
    Route::post('/login', [LoginController::class, 'apiLogin']);
    Route::post('/logout', [LoginController::class, 'apiLogout']);
    Route::get('/auth/check', [LoginController::class, 'apiCheckAuth']);
});

// SANCTUM TOKEN-BASED AUTH
Route::post('/sanctum/login', [LoginController::class, 'apiLoginSanctum']);
Route::post('/sanctum/logout', [LoginController::class, 'apiLogoutSanctum'])->middleware('auth:sanctum');
Route::get('/sanctum/auth/check', [LoginController::class, 'apiCheckAuthSanctum'])->middleware('auth:sanctum');

// ==================== PROTECTED API (PAKAI SANCTUM TOKEN) ====================

Route::middleware('auth:sanctum')->group(function () {
    
    // 🔥 USER INFO
    Route::get('/user', function () {
        return response()->json(auth()->user());
    });

    // ================================================================
    // 📡 DASHBOARD API
    // ================================================================
    
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);
    Route::get('/dashboard/uptime', [DashboardController::class, 'apiUptime']);
    Route::get('/dashboard/uptime-chart', [DashboardController::class, 'apiUptimeChart']);
    Route::get('/dashboard/smoke-chart', [DashboardController::class, 'apiSmokeChart']);
    Route::get('/dashboard/esp-status', [DashboardController::class, 'apiEspStatus']);

    // ================================================================
    // 📡 SERVICES API
    // ================================================================
    
    // CRUD Services
    Route::get('/services', [ServiceController::class, 'apiIndex']);
    Route::get('/services/{id}', [ServiceController::class, 'apiShow']);
    Route::post('/services', [ServiceController::class, 'apiStore']);
    Route::put('/services/{id}', [ServiceController::class, 'apiUpdate']);
    Route::delete('/services/{id}', [ServiceController::class, 'apiDestroy']);
    
    // Service Actions
    Route::post('/services/{id}/check', [ServiceController::class, 'apiCheck']);
    Route::get('/services/{id}/logs', [ServiceController::class, 'apiLogs']);
    Route::get('/services/{id}/detail', [ServiceController::class, 'apiDetail']);
    Route::get('/services/{id}/download-report', [ServiceController::class, 'apiDownloadReport']);

    // ================================================================
    // 📡 CONTACTS API
    // ================================================================
    
    Route::get('/contacts', [ContactController::class, 'apiIndex']);
    Route::get('/contacts/{id}', [ContactController::class, 'apiShow']);
    Route::post('/contacts', [ContactController::class, 'apiStore']);
    Route::put('/contacts/{id}', [ContactController::class, 'apiUpdate']);
    Route::delete('/contacts/{id}', [ContactController::class, 'apiDestroy']);

    // ================================================================
    // 📡 LOGS API
    // ================================================================
    
    Route::get('/logs', [LogController::class, 'apiIndex']);
    Route::get('/logs/service', [LogController::class, 'apiServiceLogs']);
    Route::get('/logs/smoke', [LogController::class, 'apiSmokeLogs']);
    Route::get('/logs/service/{id}', [LogController::class, 'apiServiceLogsById']);
    Route::get('/logs/stats', [LogController::class, 'apiStats']);

});