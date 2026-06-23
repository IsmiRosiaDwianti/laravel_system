<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Services\FonnteService;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SmokeController;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get(
    '/',
    [DashboardController::class, 'index']
)->name('dashboard');

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

Route::get(
    '/services',
    [ServiceController::class, 'index']
)->name('services');

Route::get(
    '/services/create',
    [ServiceController::class, 'create']
)->name('services.create');

Route::post(
    '/services/store',
    [ServiceController::class, 'store']
)->name('services.store');

Route::get(
    '/services/{id}/edit',
    [ServiceController::class, 'edit']
)->name('services.edit');

Route::put(
    '/services/{id}',
    [ServiceController::class, 'update']
)->name('services.update');

Route::delete(
    '/services/{id}',
    [ServiceController::class, 'destroy']
)->name('services.destroy');

Route::get(
    '/logs',
    [LogController::class, 'index']
)->name('logs');

/*
|--------------------------------------------------------------------------
| Contacts
|--------------------------------------------------------------------------
*/

Route::get(
    '/contacts',
    [ContactController::class, 'index']
)->name('contacts');

Route::get(
    '/contacts/create',
    [ContactController::class, 'create']
)->name('contacts.create');

Route::post(
    '/contacts/store',
    [ContactController::class, 'store']
)->name('contacts.store');

Route::delete(
    '/contacts/{id}',
    [ContactController::class, 'destroy']
)->name('contacts.destroy');

/*
|--------------------------------------------------------------------------
| Smoke Detector
|--------------------------------------------------------------------------
*/

Route::get(
    '/smoke-detector',
    [SmokeController::class,'index']
)->name('smoke');

/*
|--------------------------------------------------------------------------
| Test WhatsApp Fonnte
|--------------------------------------------------------------------------
*/

Route::get('/test-wa', function () {

    FonnteService::send(
        'nowhatsapp',
        'Test Laravel Monitoring berhasil 🚀'
    );

    return 'WhatsApp berhasil dikirim';
});