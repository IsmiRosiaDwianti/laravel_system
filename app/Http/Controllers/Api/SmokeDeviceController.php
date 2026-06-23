<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmokeDevice;
use Illuminate\Http\Request;

class SmokeDeviceController extends Controller
{
    public function index()
    {
        return response()->json(
            SmokeDevice::latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'threshold' => 'required|integer'
        ]);

        $device = SmokeDevice::create([
            'name' => $request->name,
            'location' => $request->location,
            'threshold' => $request->threshold,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil ditambahkan',
            'data' => $device
        ]);
    }

    public function show($id)
    {
        return response()->json(
            SmokeDevice::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $device = SmokeDevice::findOrFail($id);

        $device->update([
            'name' => $request->name,
            'location' => $request->location,
            'threshold' => $request->threshold,
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diupdate'
        ]);
    }

    public function destroy($id)
    {
        SmokeDevice::findOrFail($id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil dihapus'
        ]);
    }
}