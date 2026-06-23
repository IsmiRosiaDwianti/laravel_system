<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmokeDevice;
use App\Models\SmokeLog;
use App\Models\Contact;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class SmokeReportController extends Controller
{
    public function report(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:smoke_devices,id',
            'smoke_value' => 'required|numeric'
        ]);

        $device = SmokeDevice::findOrFail(
            $request->device_id
        );

        $oldStatus = $device->last_status;

        $status =
            $request->smoke_value >= $device->threshold
            ? 'DANGER'
            : 'NORMAL';

        $device->update([

            'last_smoke_value' => $request->smoke_value,

            'last_status' => $status,

            'device_status' => 'ONLINE',

            'last_seen_at' => now()

        ]);

        SmokeLog::create([

            'smoke_device_id' => $device->id,

            'smoke_value' => $request->smoke_value,

            'status' => $status

        ]);

        /*
        |--------------------------------------------------------------------------
        | Kirim WA Saat DANGER
        |--------------------------------------------------------------------------
        */

        if (
            $oldStatus != 'DANGER'
            &&
            $status == 'DANGER'
        ) {

            $contacts = Contact::where(
                'is_active',
                true
            )->get();

            $message =
"🚨 ASAP TERDETEKSI

📍 Lokasi : {$device->location}

📟 Device : {$device->name}

🌫 Nilai Asap : {$request->smoke_value}

⚠ Threshold : {$device->threshold}

🚨 Status : DANGER

🕐 ".now()->format('d-m-Y H:i:s');

            foreach ($contacts as $contact) {

                FonnteService::send(
                    $contact->phone,
                    $message
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Kirim WA Saat Kembali Normal
        |--------------------------------------------------------------------------
        */

        if (
            $oldStatus == 'DANGER'
            &&
            $status == 'NORMAL'
        ) {

            $contacts = Contact::where(
                'is_active',
                true
            )->get();

            $message =
"✅ KONDISI KEMBALI NORMAL

📍 Lokasi : {$device->location}

📟 Device : {$device->name}

🌫 Nilai Asap : {$request->smoke_value}

🕐 ".now()->format('d-m-Y H:i:s');

            foreach ($contacts as $contact) {

                FonnteService::send(
                    $contact->phone,
                    $message
                );
            }
        }

        return response()->json([

            'success' => true,

            'device' => $device->name,

            'smoke_value' => $request->smoke_value,

            'status' => $status,

            'device_status' => 'ONLINE',

            'timestamp' => now()

        ]);
    }
}