<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmokeLog;

class SmokeLogApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => SmokeLog::with('device')
                ->latest()
                ->paginate(50)
        ]);
    }

    public function show($id)
    {
        $log = SmokeLog::with('device')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }
}