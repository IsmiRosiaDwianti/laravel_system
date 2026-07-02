<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Services\FonnteService;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SmokeController;
use App\Http\Controllers\LoginController;  

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (Harus Login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    */

    Route::get('/services', [ServiceController::class, 'index'])->name('services');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // ===== NEW ROUTES FOR SERVICE DETAILS & REPORTS =====
    Route::get('/services/{id}/detail', [ServiceController::class, 'detail'])->name('services.detail');
    Route::get('/services/{id}/logs', [ServiceController::class, 'logs'])->name('services.logs');
    Route::post('/services/{id}/check', [ServiceController::class, 'check'])->name('services.check');
    Route::get('/services/overview', [ServiceController::class, 'overview'])->name('services.overview');
    Route::get('/services/export', [ServiceController::class, 'export'])->name('services.export');
    Route::delete('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
    Route::get('/services/{id}/health', [ServiceController::class, 'health'])->name('services.health');
    Route::get('/services/{id}/download-report', [ServiceController::class, 'downloadReport'])->name('services.download-report');

    /*
    |--------------------------------------------------------------------------
    | Logs
    |--------------------------------------------------------------------------
    */

    Route::get('/logs', [LogController::class, 'index'])->name('logs');

    /*
    |--------------------------------------------------------------------------
    | Contacts
    |--------------------------------------------------------------------------
    */

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    /*
    |--------------------------------------------------------------------------
    | Smoke Detector
    |--------------------------------------------------------------------------
    */

    // 🔥 HALAMAN SMOKE DETECTOR
    Route::get('/smoke-detector', [SmokeController::class, 'index'])->name('smoke');
    // 📥 EXPORT SMOKE LOGS KE CSV
    Route::get('/smoke-detector/export', [SmokeController::class, 'export'])->name('smoke.export');
});

/*
|--------------------------------------------------------------------------
| Test WhatsApp Fonnte
|--------------------------------------------------------------------------
*/

Route::get('/test-wa', function () {
    FonnteService::send(
        'nomorsaya',
        'Test Laravel Monitoring berhasil 🚀'
    );
    return 'WhatsApp berhasil dikirim';
});

/*
|==========================================================================
| 🔥 API ROUTES UNTUK SMOKE DETECTOR (ESP32)
|==========================================================================
*/

Route::prefix('api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 🔥 SMOKE DETECTOR - TANPA AUTH (Untuk ESP32)
    |--------------------------------------------------------------------------
    |  📡 ESP32 Kirim Data: POST /api/smoke
    |  Body: { "ppm": 350 }
    |  
    |  📊 Cek Status: GET /api/smoke/status
    |  📜 Cek Logs: GET /api/smoke/logs
    |--------------------------------------------------------------------------
    */

    // 🔥 ESP32 KIRIM DATA (POST) - URL MUDAH!
    Route::post('/smoke', [SmokeController::class, 'receiveData']);

    // 📊 AMBIL STATUS TERBARU (GET)
    Route::get('/smoke/status', [SmokeController::class, 'getStatus']);

    // 📜 AMBIL HISTORY LOGS (GET)
    Route::get('/smoke/logs', [SmokeController::class, 'getLogs']);

    /*
    |--------------------------------------------------------------------------
    | Auth API (Tetap di sini)
    |--------------------------------------------------------------------------
    */

    Route::post('/login', [LoginController::class, 'apiLogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [LoginController::class, 'apiLogout']);
        Route::get('/user', function () {
            return response()->json(auth()->user());
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Services API (Butuh Auth)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/services', [ServiceController::class, 'apiIndex']);
        Route::get('/services/{service}', [ServiceController::class, 'apiShow']);
        Route::post('/services', [ServiceController::class, 'apiStore']);
        Route::put('/services/{service}', [ServiceController::class, 'apiUpdate']);
        Route::delete('/services/{service}', [ServiceController::class, 'apiDestroy']);

        Route::get('/services/{id}/detail', [ServiceController::class, 'detail']);
        Route::get('/services/{id}/logs', [ServiceController::class, 'logs']);
        Route::post('/services/{id}/check', [ServiceController::class, 'check']);
        Route::get('/services/overview', [ServiceController::class, 'overview']);
        Route::get('/services/{id}/health', [ServiceController::class, 'health']);
        Route::get('/services/{id}/download-report', [ServiceController::class, 'downloadReport']);
    });

    /*
    |--------------------------------------------------------------------------
    | Contacts API (Butuh Auth)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/contacts', [ContactController::class, 'apiIndex']);
        Route::get('/contacts/{contact}', [ContactController::class, 'apiShow']);
        Route::post('/contacts', [ContactController::class, 'apiStore']);
        Route::put('/contacts/{contact}', [ContactController::class, 'apiUpdate']);
        Route::delete('/contacts/{contact}', [ContactController::class, 'apiDestroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Logs API (Butuh Auth)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/logs', [LogController::class, 'apiIndex']);
        Route::get('/logs/service', [LogController::class, 'apiServiceLogs']);
        Route::get('/logs/smoke', [LogController::class, 'apiSmokeLogs']);
        Route::get('/logs/service/{id}', [LogController::class, 'apiServiceLogsById']);
        Route::get('/logs/summary', [LogController::class, 'apiSummary']);
        Route::get('/logs/stats', [LogController::class, 'apiStats']);
    });

    /*
    |--------------------------------------------------------------------------
    | Dashboard API (Butuh Auth)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);
        Route::get('/dashboard/uptime', [DashboardController::class, 'apiUptime']);
        Route::get('/dashboard/charts', [DashboardController::class, 'apiCharts']);
        Route::get('/dashboard/recent-issues', [DashboardController::class, 'apiRecentIssues']);
        Route::get('/dashboard/service-health', [DashboardController::class, 'apiServiceHealth']);
    });
});