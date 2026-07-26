<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmokeApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\LogApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\RegisterApiController;
use App\Http\Controllers\NetworkController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes - Monitoring System DISKOMINFOTIK
|--------------------------------------------------------------------------
*/

// ================================================================
// 1️⃣ PUBLIC API (TANPA AUTHENTIKASI)
// ================================================================

Route::withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
    ->group(function () {
        
        // ============================================================
        // 🔥 SMOKE DETECTOR API (Untuk ESP32)
        // ============================================================
        Route::post('/smoke', [SmokeApiController::class, 'receiveData'])
            ->middleware('throttle:60,1');

        Route::get('/smoke/status', [SmokeApiController::class, 'getStatus']);
        Route::get('/smoke/logs', [SmokeApiController::class, 'getLogs']);
        Route::get('/smoke/check-esp-status', [SmokeApiController::class, 'checkEspStatus']);

        // ============================================================
        // 🌐 NETWORK STATUS
        // ============================================================
        Route::get('/network/status', [NetworkController::class, 'status']);

        // ============================================================
        // 🖥️ SERVICES STATUS
        // ============================================================
        Route::get('/services/status', [ServiceApiController::class, 'status']);

        // ============================================================
        // 🧪 TEST API
        // ============================================================
        Route::get('/test-api', function () {
            return response()->json([
                'success' => true,
                'message' => 'API Laravel berjalan',
                'timestamp' => now()->toDateTimeString()
            ]);
        });
    });

// ================================================================
// 2️⃣ AUTHENTICATION ROUTES
// ================================================================

// ============================================================
// 🔐 SESSION-BASED AUTH (Untuk Web)
// ============================================================
Route::middleware(['web'])->group(function () {
    Route::post('/login', [LoginApiController::class, 'login']);
    Route::post('/register', [RegisterApiController::class, 'register']);
    Route::post('/logout', [LoginApiController::class, 'logout']);
    Route::get('/auth/check', [LoginApiController::class, 'checkAuth']);
});

// ============================================================
// 🔑 TOKEN-BASED AUTH (Untuk Mobile/Postman)
// ============================================================
Route::post('/sanctum/login', [LoginApiController::class, 'loginSanctum']);
// 🔥 HAPUS ROUTE INI KARENA METHOD SUDAH DIHAPUS!
// Route::post('/sanctum/register', [RegisterApiController::class, 'registerSanctum']);

Route::post('/sanctum/logout', [LoginApiController::class, 'logoutSanctum'])->middleware('auth:sanctum');
Route::get('/sanctum/auth/check', [LoginApiController::class, 'checkAuthSanctum'])->middleware('auth:sanctum');

// ================================================================
// 3️⃣ PROTECTED API (MEMERLUKAN TOKEN)
// ================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // ============================================================
    // 👤 USER PROFILE
    // ============================================================
    Route::get('/user', function () {
        return response()->json(auth()->user());
    });

    // ============================================================
    // 📊 DASHBOARD API
    // ============================================================
    Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
    Route::get('/dashboard/uptime', [DashboardApiController::class, 'uptime']);
    Route::get('/dashboard/uptime-chart', [DashboardApiController::class, 'uptimeChart']);
    Route::get('/dashboard/smoke-chart', [DashboardApiController::class, 'smokeChart']);
    Route::get('/dashboard/esp-status', [DashboardApiController::class, 'espStatus']);

    // ============================================================
    // 🖥️ SERVICES API (CRUD)
    // ============================================================
    Route::get('/services', [ServiceApiController::class, 'index']);
    Route::get('/services/{id}', [ServiceApiController::class, 'show']);
    Route::post('/services', [ServiceApiController::class, 'store']);
    Route::put('/services/{id}', [ServiceApiController::class, 'update']);
    Route::delete('/services/{id}', [ServiceApiController::class, 'destroy']);
    
    Route::get('/services/search', [ServiceApiController::class, 'search']);
    Route::post('/services/{id}/check', [ServiceApiController::class, 'check']);
    Route::get('/services/{id}/logs', [ServiceApiController::class, 'logs']);
    Route::get('/services/{id}/detail', [ServiceApiController::class, 'detail']);
    Route::get('/services/{id}/download-report', [ServiceApiController::class, 'downloadReport']);

    // ============================================================
    // 📞 CONTACTS API
    // ============================================================
    Route::get('/contacts', [ContactApiController::class, 'index']);
    Route::get('/contacts/{id}', [ContactApiController::class, 'show']);
    Route::post('/contacts', [ContactApiController::class, 'store']);
    Route::put('/contacts/{id}', [ContactApiController::class, 'update']);
    Route::delete('/contacts/{id}', [ContactApiController::class, 'destroy']);
    Route::get('/contacts/search', [ContactApiController::class, 'search']);

    // ============================================================
    // 📋 LOGS API
    // ============================================================
    Route::get('/logs', [LogApiController::class, 'index']);
    Route::get('/logs/service', [LogApiController::class, 'serviceLogs']);
    Route::get('/logs/smoke', [LogApiController::class, 'smokeLogs']);
    Route::get('/logs/service/{id}', [LogApiController::class, 'serviceLogsById']);
    Route::get('/logs/stats', [LogApiController::class, 'stats']);

    // ============================================================
    // 🔥 SMOKE DETECTOR API (Protected)
    // ============================================================
    Route::post('/smoke/export', [SmokeApiController::class, 'export']);
});