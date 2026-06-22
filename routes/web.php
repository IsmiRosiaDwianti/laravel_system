<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Services\FonnteService;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Monitoring System DISKOMINFOTIK Provinsi Lampung
|
*/

Route::get('/',
    [DashboardController::class, 'index'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

Route::get('/services',
    [ServiceController::class, 'index'])
    ->name('services');

Route::get('/services/create',
    [ServiceController::class, 'create'])
    ->name('services.create');

Route::post('/services/store',
    [ServiceController::class, 'store'])
    ->name('services.store');

Route::get('/services/{id}/edit',
    [ServiceController::class, 'edit'])
    ->name('services.edit');

Route::put('/services/{id}',
    [ServiceController::class, 'update'])
    ->name('services.update');

Route::delete('/services/{id}',
    [ServiceController::class, 'destroy'])
    ->name('services.destroy');

/*
|--------------------------------------------------------------------------
| Contacts
|--------------------------------------------------------------------------
*/

Route::view('/contacts', 'contacts')
    ->name('contacts');

/*
|--------------------------------------------------------------------------
| Smoke Detector
|--------------------------------------------------------------------------
*/

Route::view('/smoke-detector', 'smoke')
    ->name('smoke');

Route::get('/test-wa', function () {

    FonnteService::send(
        '6282181026804',
        'Test Laravel Monitoring berhasil 🚀'
    );

    return 'WA terkirim';
});