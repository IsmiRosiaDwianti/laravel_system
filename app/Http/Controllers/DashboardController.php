<?php

namespace App\Http\Controllers;

use App\Models\Service;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('dashboard', compact(
            'total',
            'up',
            'warning',
            'down'
        ));
    }
}