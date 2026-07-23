<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmokeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes - Monitoring System DISKOMINFOTIK
|--------------------------------------------------------------------------
|
| Berikut adalah semua endpoint API untuk sistem monitoring.
| Dibagi menjadi 3 kategori:
| 1. PUBLIC API (Tanpa Auth) - Untuk ESP32 dan pengecekan jaringan
| 2. AUTH ROUTES - Login, Register, Logout
| 3. PROTECTED API (dengan Sanctum Token) - Data sensitif
|
*/

// ================================================================
// 1️⃣ PUBLIC API (TANPA AUTHENTIKASI)
// ================================================================
// Endpoint ini bisa diakses oleh siapa saja, termasuk ESP32 dan
// pengecekan jaringan dari frontend (JavaScript)
// ================================================================

Route::withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])
    ->group(function () {
        
        // ============================================================
        // 🔥 SMOKE DETECTOR API (Untuk ESP32)
        // ============================================================
        // Digunakan oleh perangkat ESP32 untuk mengirim data detektor asap
        // dan mengecek status perangkat secara real-time
        // ============================================================
        
        // 📤 ESP32 mengirim data detektor asap ke sini
        Route::post('/smoke', [SmokeController::class, 'receiveData'])
            ->middleware('throttle:60,1'); // Maksimal 60 request per menit

        // 📊 Cek status detektor asap secara keseluruhan
        Route::get('/smoke/status', [SmokeController::class, 'getStatus']);
        
        // 📋 Ambil semua log detektor asap
        Route::get('/smoke/logs', [SmokeController::class, 'getLogs']);
        
        // ✅ Cek apakah ESP dalam keadaan online/offline
        Route::get('/smoke/check-esp-status', [SmokeController::class, 'checkEspStatus']);
        
        // 🔍 Cek satu perangkat ESP tertentu berdasarkan ID
        Route::get('/smoke/device/{id}', [SmokeController::class, 'checkSingleDevice']);

        // ============================================================
        // 🌐 NETWORK STATUS (Untuk Pengecekan Internet)
        // ============================================================
        // Digunakan oleh frontend (JavaScript) untuk mengecek apakah
        // server terhubung ke internet atau tidak. Dicek setiap 30 detik.
        // ============================================================
        Route::get('/network/status', [NetworkController::class, 'status']);

        // ============================================================
        // 🖥️ SERVICES STATUS (Untuk Pengecekan Service)
        // ============================================================
        // Digunakan oleh frontend (JavaScript) untuk mengecek status
        // semua service yang dimonitoring. Dipanggil dari dashboard.
        // ============================================================
        Route::get('/services/status', [ServiceController::class, 'apiStatus']);

        // ============================================================
        // 🧪 TEST API (Untuk Debugging)
        // ============================================================
        // Endpoint sederhana untuk mengecek apakah API berjalan normal
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
// Endpoint untuk login, register, dan logout pengguna
// ================================================================

// ============================================================
// 🔐 SESSION-BASED AUTH (Untuk Web)
// ============================================================
// Menggunakan session Laravel untuk autentikasi via browser
// ============================================================
Route::middleware(['web'])->group(function () {
    
    // Login - Menggunakan session cookie
    Route::post('/login', [LoginController::class, 'apiLogin'])->name('api.login');
    
    // Register - Mendaftar akun baru
    Route::post('/register', [RegisterController::class, 'apiRegister'])->name('api.register');
    
    // Logout - Menghapus session
    Route::post('/logout', [LoginController::class, 'apiLogout'])->name('api.logout');
    
    // Cek status autentikasi
    Route::get('/auth/check', [LoginController::class, 'apiCheckAuth'])->name('api.auth.check');
});

// ============================================================
// 🔑 TOKEN-BASED AUTH (Untuk Mobile/Postman)
// ============================================================
// Menggunakan Sanctum token untuk autentikasi via API
// Cocok untuk mobile app, Postman, atau integrasi dengan sistem lain
// ============================================================
Route::post('/sanctum/login', [LoginController::class, 'apiLoginSanctum'])->name('api.sanctum.login');
Route::post('/sanctum/register', [LoginController::class, 'apiRegisterSanctum'])->name('api.sanctum.register');
Route::post('/sanctum/logout', [LoginController::class, 'apiLogoutSanctum'])->middleware('auth:sanctum')->name('api.sanctum.logout');
Route::get('/sanctum/auth/check', [LoginController::class, 'apiCheckAuthSanctum'])->middleware('auth:sanctum')->name('api.sanctum.auth.check');

// ================================================================
// 3️⃣ PROTECTED API (MEMERLUKAN TOKEN)
// ================================================================
// Semua endpoint di bawah ini membutuhkan token Sanctum yang valid.
// Token dikirim melalui header: Authorization: Bearer {token}
// ================================================================

Route::middleware('auth:sanctum')->group(function () {
    
    // ============================================================
    // 👤 USER PROFILE
    // ============================================================
    // Mendapatkan data pengguna yang sedang login
    // ============================================================
    Route::get('/user', function () {
        return response()->json(auth()->user());
    });

    // ============================================================
    // 📊 DASHBOARD API
    // ============================================================
    // Endpoint untuk menampilkan data di halaman dashboard
    // ============================================================
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats']);           // Statistik dashboard
    Route::get('/dashboard/uptime', [DashboardController::class, 'apiUptime']);         // Data uptime
    Route::get('/dashboard/uptime-chart', [DashboardController::class, 'apiUptimeChart']); // Chart uptime
    Route::get('/dashboard/smoke-chart', [DashboardController::class, 'apiSmokeChart']);   // Chart smoke detector
    Route::get('/dashboard/esp-status', [DashboardController::class, 'apiEspStatus']);     // Status ESP

    // ============================================================
    // 🖥️ SERVICES API (CRUD Service Monitoring)
    // ============================================================
    // Mengelola service yang dimonitoring (PING / HTTP)
    // ============================================================
    
    // 📋 CRUD Services
    Route::get('/services', [ServiceController::class, 'apiIndex']);                    // Daftar semua service
    Route::get('/services/{id}', [ServiceController::class, 'apiShow']);                // Detail satu service
    Route::post('/services', [ServiceController::class, 'apiStore']);                   // Tambah service baru
    Route::put('/services/{id}', [ServiceController::class, 'apiUpdate']);              // Update service
    Route::delete('/services/{id}', [ServiceController::class, 'apiDestroy']);          // Hapus service
    
    // 🔍 Search
    Route::get('/services/search', [ServiceController::class, 'apiSearch']);            // Cari service
    
    // ⚡ Service Actions
    Route::post('/services/check-all', [ServiceController::class, 'apiCheckAll']);      // Cek semua service sekaligus
    Route::post('/services/{id}/check', [ServiceController::class, 'apiCheck']);        // Cek satu service
    Route::get('/services/{id}/logs', [ServiceController::class, 'apiLogs']);           // Log service tertentu
    Route::get('/services/{id}/detail', [ServiceController::class, 'apiDetail']);       // Detail lengkap
    Route::get('/services/{id}/download-report', [ServiceController::class, 'apiDownloadReport']); // Download laporan

    // ============================================================
    // 📞 CONTACTS API (Manajemen Kontak WhatsApp)
    // ============================================================
    // Mengelola kontak untuk notifikasi WhatsApp
    // ============================================================
    Route::get('/contacts', [ContactController::class, 'apiIndex']);                    // Daftar semua kontak
    Route::get('/contacts/{id}', [ContactController::class, 'apiShow']);                // Detail kontak
    Route::post('/contacts', [ContactController::class, 'apiStore']);                   // Tambah kontak baru
    Route::put('/contacts/{id}', [ContactController::class, 'apiUpdate']);              // Update kontak
    Route::delete('/contacts/{id}', [ContactController::class, 'apiDestroy']);          // Hapus kontak
    Route::get('/contacts/search', [ContactController::class, 'apiSearch']);            // Cari kontak

    // ============================================================
    // 📋 LOGS API (Riwayat Monitoring)
    // ============================================================
    // Mengambil data log dari semua aktivitas monitoring
    // ============================================================
    Route::get('/logs', [LogController::class, 'apiIndex']);                            // Semua log
    Route::get('/logs/service', [LogController::class, 'apiServiceLogs']);              // Log service
    Route::get('/logs/smoke', [LogController::class, 'apiSmokeLogs']);                  // Log smoke detector
    Route::get('/logs/service/{id}', [LogController::class, 'apiServiceLogsById']);     // Log service by ID
    Route::get('/logs/stats', [LogController::class, 'apiStats']);                      // Statistik log

    // ============================================================
    // 🔥 SMOKE DETECTOR API (Protected)
    // ============================================================
    // Endpoint tambahan untuk data detektor asap yang butuh auth
    // ============================================================
    Route::get('/smoke/history', [SmokeController::class, 'getHistory']);               // Riwayat lengkap
    Route::get('/smoke/latest', [SmokeController::class, 'getLatest']);                 // Data terbaru
    Route::get('/smoke/stats', [SmokeController::class, 'getStats']);                   // Statistik detektor
    Route::post('/smoke/export', [SmokeController::class, 'export']);                   // Export data
});
