<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmokeDeviceController;
use App\Http\Controllers\Api\SmokeReportController;

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