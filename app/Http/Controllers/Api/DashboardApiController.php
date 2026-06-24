<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmokeDevice;
use App\Models\Service;

class DashboardApiController extends Controller
{
    public function index()
    {
        $totalDevice = SmokeDevice::count();

        $onlineDevice = SmokeDevice::where(
            'device_status',
            'ONLINE'
        )->count();

        $offlineDevice = SmokeDevice::where(
            'device_status',
            'OFFLINE'
        )->count();

        $dangerDevice = SmokeDevice::where(
            'last_status',
            'DANGER'
        )->count();

        $totalService = Service::count();

        $upService = Service::where(
            'last_status',
            'UP'
        )->count();

        $downService = Service::where(
            'last_status',
            'DOWN'
        )->count();

        $warningService = Service::where(
            'last_status',
            'WARNING'
        )->count();

        return response()->json([
            'success' => true,

            'devices' => [
                'total' => $totalDevice,
                'online' => $onlineDevice,
                'offline' => $offlineDevice,
                'danger' => $dangerDevice,
            ],

            'services' => [
                'total' => $totalService,
                'up' => $upService,
                'down' => $downService,
                'warning' => $warningService,
            ]
        ]);
    }
}