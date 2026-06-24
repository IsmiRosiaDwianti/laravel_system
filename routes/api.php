<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SmokeDeviceController;
use App\Http\Controllers\Api\SmokeReportController;

use App\Http\Controllers\Api\ContactApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\LogApiController;
use App\Http\Controllers\Api\DashboardApiController;

/*
|--------------------------------------------------------------------------
| Dashboard API
|--------------------------------------------------------------------------
*/

Route::get(
    '/dashboard',
    [DashboardApiController::class, 'index']
);

/*
|--------------------------------------------------------------------------
| Contact CRUD API
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'contacts',
    ContactApiController::class
);

/*
|--------------------------------------------------------------------------
| Service CRUD API
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'services',
    ServiceApiController::class
);

/*
|--------------------------------------------------------------------------
| Manual Check Service
|--------------------------------------------------------------------------
*/

Route::post(
    '/services/{service}/check',
    [ServiceApiController::class, 'check']
);

/*
|--------------------------------------------------------------------------
| Logs API
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'logs',
    LogApiController::class
)->only([
    'index',
    'show'
]);

/*
|--------------------------------------------------------------------------
| Smoke Device CRUD API
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'smoke-devices',
    SmokeDeviceController::class
);

/*
|--------------------------------------------------------------------------
| Smoke Sensor Report API (ESP32)
|--------------------------------------------------------------------------
*/

Route::post(
    '/smoke/report',
    [SmokeReportController::class, 'report']
);

/*
|--------------------------------------------------------------------------
| Test API
|--------------------------------------------------------------------------
*/

Route::get('/test-api', function () {

    return response()->json([
        'success' => true,
        'message' => 'API Laravel berjalan'
    ]);

});
