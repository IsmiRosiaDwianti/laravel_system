<?php

namespace App\Http\Controllers;

use App\Models\SmokeDevice;
use App\Models\SmokeLog;

class SmokeController extends Controller
{
    public function index()
    {
        $devices = SmokeDevice::all();

        $totalDevice = SmokeDevice::count();

        $online = SmokeDevice::where(
            'device_status',
            'ONLINE'
        )->count();

        $offline = SmokeDevice::where(
            'device_status',
            'OFFLINE'
        )->count();

        $danger = SmokeDevice::where(
            'last_status',
            'DANGER'
        )->count();

        $chartLogs = SmokeLog::latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        return view(
            'smoke',
            compact(
                'devices',
                'totalDevice',
                'online',
                'offline',
                'danger',
                'chartLogs'
            )
        );
    }
}