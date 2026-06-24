<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Services\ServiceMonitorService;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::latest()->get();

        return view(
            'services',
            compact('services')
        );
    }

    public function create()
    {
        return view('service_create');
    }

    public function store(
        Request $request,
        ServiceMonitorService $monitor
    )
    {
        $request->validate([
            'name' => 'required',
            'target' => 'required',
            'type' => 'required'
        ]);

        $service = Service::create([
            'name' => $request->name,
            'target' => $request->target,
            'type' => $request->type,
            'last_status' => 'UNKNOWN'
        ]);

        $monitor->check($service);

        return redirect()
            ->route('services')
            ->with(
                'success',
                'Service berhasil ditambahkan dan langsung dicek'
            );
    }

    public function edit($id)
    {
        $service = Service::findOrFail($id);

        return view(
            'service_edit',
            compact('service')
        );
    }

    public function update(
        Request $request,
        $id,
        ServiceMonitorService $monitor
    )
    {
        $service = Service::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'target' => 'required',
            'type' => 'required'
        ]);

        $service->update([
            'name' => $request->name,
            'target' => $request->target,
            'type' => $request->type
        ]);

        $monitor->check($service);

        return redirect()
            ->route('services')
            ->with(
                'success',
                'Service berhasil diupdate dan langsung dicek'
            );
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        $service->delete();

        return redirect()
            ->route('services')
            ->with(
                'success',
                'Service berhasil dihapus'
            );
    }
}