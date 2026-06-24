<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Services\ServiceMonitorService;

class ServiceApiController extends Controller
{
    /**
     * GET /api/services
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Service::latest()->get()
        ]);
    }

    /**
     * POST /api/services
     */
    public function store(
        Request $request,
        ServiceMonitorService $monitor
    ) {
        $request->validate([
            'name'   => 'required',
            'target' => 'required',
            'type'   => 'required|in:http,ping'
        ]);

        $service = Service::create([
            'name' => $request->name,
            'target' => $request->target,
            'type' => $request->type,
            'last_status' => 'UNKNOWN'
        ]);

        // langsung cek setelah dibuat
        $monitor->check($service);

        return response()->json([
            'success' => true,
            'message' => 'Service berhasil dibuat',
            'data' => $service->fresh()
        ], 201);
    }

    /**
     * GET /api/services/{id}
     */
    public function show(string $id)
    {
        $service = Service::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    /**
     * PUT /api/services/{id}
     */
    public function update(
        Request $request,
        string $id,
        ServiceMonitorService $monitor
    ) {
        $service = Service::findOrFail($id);

        $request->validate([
            'name'   => 'required',
            'target' => 'required',
            'type'   => 'required|in:http,ping'
        ]);

        $service->update([
            'name' => $request->name,
            'target' => $request->target,
            'type' => $request->type
        ]);

        // cek ulang setelah update
        $monitor->check($service);

        return response()->json([
            'success' => true,
            'message' => 'Service berhasil diupdate',
            'data' => $service->fresh()
        ]);
    }

    /**
     * DELETE /api/services/{id}
     */
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service berhasil dihapus'
        ]);
    }
}