<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Services\FonnteService;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SmokeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NetworkController;

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

    // 🔍 SEARCH ROUTE
    Route::get('/services/search', [ServiceController::class, 'search'])->name('services.search');

    // 🔥 CHECK ALL SERVICES (untuk auto-check di frontend)
    Route::post('/services/check-all', [ServiceController::class, 'checkAll'])->name('services.check-all');

    // Service Details & Reports
    Route::get('/services/{id}/detail', [ServiceController::class, 'detail'])->name('services.detail');
    Route::get('/services/{id}/logs', [ServiceController::class, 'logs'])->name('services.logs');
    Route::post('/services/{id}/check', [ServiceController::class, 'check'])->name('services.check');
    Route::get('/services/overview', [ServiceController::class, 'overview'])->name('services.overview');
    Route::get('/services/export', [ServiceController::class, 'export'])->name('services.export');
    Route::delete('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
    Route::get('/services/{id}/health', [ServiceController::class, 'health'])->name('services.health');
    Route::get('/services/{id}/download-report', [ServiceController::class, 'downloadReport'])->name('services.download-report');

    // 🔥 🔥 🔥 TAMBAHKAN ROUTE INI!
    Route::post('/services/{id}/wa-interval', [ServiceController::class, 'updateWaInterval'])->name('services.wa-interval');

    /*
    |--------------------------------------------------------------------------
    | 🔥 API STATUS (untuk AJAX Polling Frontend)
    |--------------------------------------------------------------------------
    */
    Route::get('/api/services/status', [ServiceController::class, 'apiStatus'])->name('api.services.status');

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

    // 🔍 SEARCH ROUTE CONTACT
    Route::get('/contacts/search', [ContactController::class, 'search'])->name('contacts.search');

    /*
    |--------------------------------------------------------------------------
    | Smoke Detector (Web View)
    |--------------------------------------------------------------------------
    */
    Route::get('/smoke-detector', [SmokeController::class, 'index'])->name('smoke');
    Route::get('/smoke-detector/export', [SmokeController::class, 'export'])->name('smoke.export');

    /*
    |--------------------------------------------------------------------------
    | 🔥 NETWORK STATUS API
    |--------------------------------------------------------------------------
    */
    Route::get('/api/network/status', [NetworkController::class, 'status'])->name('api.network.status');
});

/*
|--------------------------------------------------------------------------
| API Routes (Untuk Postman / Mobile App)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | API: Services
    |--------------------------------------------------------------------------
    */
    Route::get('/services', [ServiceController::class, 'apiIndex']);
    Route::get('/services/{id}', [ServiceController::class, 'apiShow']);
    Route::post('/services', [ServiceController::class, 'apiStore']);
    Route::put('/services/{id}', [ServiceController::class, 'apiUpdate']);
    Route::delete('/services/{id}', [ServiceController::class, 'apiDestroy']);
    Route::post('/services/{id}/check', [ServiceController::class, 'apiCheck']);
    Route::get('/services/{id}/logs', [ServiceController::class, 'apiLogs']);
    Route::get('/services/{id}/detail', [ServiceController::class, 'apiDetail']);
    Route::get('/services/{id}/download-report', [ServiceController::class, 'apiDownloadReport']);

    // 🔍 API SEARCH SERVICES
    Route::get('/services/search', [ServiceController::class, 'apiSearch']);

    // 🔥 API STATUS (untuk polling)
    Route::get('/services/status', [ServiceController::class, 'apiStatus']);

    // 🔥 API CHECK ALL SERVICES
    Route::post('/services/check-all', [ServiceController::class, 'apiCheckAll']);

    /*
    |--------------------------------------------------------------------------
    | API: Contacts
    |--------------------------------------------------------------------------
    */
    Route::get('/contacts', [ContactController::class, 'apiIndex']);
    Route::get('/contacts/{id}', [ContactController::class, 'apiShow']);
    Route::post('/contacts', [ContactController::class, 'apiStore']);
    Route::put('/contacts/{id}', [ContactController::class, 'apiUpdate']);
    Route::delete('/contacts/{id}', [ContactController::class, 'apiDestroy']);

    // 🔍 API SEARCH CONTACTS
    Route::get('/contacts/search', [ContactController::class, 'apiSearch']);

    /*
    |--------------------------------------------------------------------------
    | API: Network Status
    |--------------------------------------------------------------------------
    */
    Route::get('/network/status', [NetworkController::class, 'status']);
});

/*
|--------------------------------------------------------------------------
| Test WhatsApp (Temporary)
|--------------------------------------------------------------------------
*/
Route::get('/test-wa', function () {
    FonnteService::send(
        '6281234567890',
        'Test Laravel Monitoring berhasil 🚀'
    );
    return 'WhatsApp berhasil dikirim';
});