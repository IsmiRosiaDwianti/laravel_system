<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Statistik Dashboard
        |--------------------------------------------------------------------------
        */

        $total = Service::count();

        $up = Service::where(
            'last_status',
            'UP'
        )->count();

        $warning = Service::where(
            'last_status',
            'WARNING'
        )->count();

        $down = Service::where(
            'last_status',
            'DOWN'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Service Terbaru
        |--------------------------------------------------------------------------
        */

        $latestServices = Service::latest()
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Data Grafik Line Chart
        |--------------------------------------------------------------------------
        */

        $logs = ServiceLog::latest()
            ->take(20)
            ->get()
            ->reverse();

        $chartLabels = [];

        $chartTimes = [];

        foreach ($logs as $log) {

            $chartLabels[] =
                $log->created_at->format('H:i');

            $chartTimes[] =
                $log->response_time ?? 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Kirim ke View
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard',
            compact(
                'total',
                'up',
                'warning',
                'down',
                'latestServices',
                'chartLabels',
                'chartTimes'
            )
        );
    }
}