<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceLog;

class ServiceLogApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ServiceLog::with('service')
                ->latest()
                ->paginate(50)
        ]);
    }

    public function show($id)
    {
        $log = ServiceLog::with('service')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }
}