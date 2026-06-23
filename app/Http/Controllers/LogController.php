<?php

namespace App\Http\Controllers;

use App\Models\ServiceLog;

class LogController extends Controller
{
    public function index()
    {
        $logs = ServiceLog::with('service')
            ->latest()
            ->paginate(50);

        return view(
            'logs',
            compact('logs')
        );
    }
}