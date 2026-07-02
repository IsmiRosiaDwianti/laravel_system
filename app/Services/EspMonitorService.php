<?php

namespace App\Services;

use App\Models\SmokeDevice;
use App\Models\SmokeLog;
use App\Models\Contact;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EspMonitorService
{
    /**
     * CEK STATUS ESP - Apakah mengirim data dalam 2 menit terakhir
     * Sinkron dengan dashboard (2 menit)
     */
    public function checkEspStatus()
    {
        // 🔥 Ambil semua device
        $devices = SmokeDevice::all();

        if ($devices->isEmpty()) {
            Log::info('📡 Tidak ada device ESP yang terdaftar');
            return;
        }

        foreach ($devices as $device) {
            // Cek apakah device online (kirim data dalam 2 menit terakhir)
            $isOnline = $device->last_seen_at &&
                        Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

            // 🔥 UPDATE STATUS DI DATABASE (sinkron dengan dashboard)
            $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
            $device->save();

            // Jika OFFLINE (tidak kirim data > 2 menit)
            if (!$isOnline) {
                // Ambil data terakhir dari device ini
                $lastLog = SmokeLog::where('device_id', $device->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Hitung sudah berapa menit tidak kirim data
                $lastSeen = $device->last_seen_at
                    ? Carbon::parse($device->last_seen_at)
                    : null;

                $minutesDiff = $lastSeen
                    ? $lastSeen->diffInMinutes(now())
                    : 'TIDAK ADA DATA';

                // 🔥 CEK APAKAH SUDAH PERNAH KIRIM ALERT SEBELUMNYA
                // Cek log terakhir untuk device ini (hindari spam)
                $lastAlertLog = SmokeLog::where('device_id', $device->id)
                    ->where('message', 'LIKE', '%OFFLINE%')
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Jika belum pernah kirim alert atau sudah lebih dari 30 menit
                $shouldSendAlert = true;
                if ($lastAlertLog) {
                    $lastAlertTime = Carbon::parse($lastAlertLog->created_at);
                    $minutesSinceLastAlert = $lastAlertTime->diffInMinutes(now());
                    
                    // Jika alert terakhir kurang dari 30 menit, skip (hindari spam)
                    if ($minutesSinceLastAlert < 30) {
                        $shouldSendAlert = false;
                        Log::info("⏳ Skip alert untuk {$device->name}, terakhir kirim {$minutesSinceLastAlert} menit lalu");
                    }
                }

                // Kirim alert jika memenuhi syarat
                if ($shouldSendAlert) {
                    $this->sendEspAlert($device, $lastLog, $minutesDiff);
                }
            }
        }

        Log::info('✅ ESP monitoring selesai, total device: ' . $devices->count());
    }

    /**
     * KIRIM ALERT ESP VIA WHATSAPP
     */
    private function sendEspAlert($device, $lastLog = null, $minutesDiff)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA ESP alert');
            return;
        }

        // Data terakhir
        $lastSmoke = $lastLog ? $lastLog->smoke_value : 'N/A';
        $lastStatus = $lastLog ? $lastLog->status : 'N/A';
        $lastTime = $device->last_seen_at
            ? Carbon::parse($device->last_seen_at)->format('d-m-Y H:i:s')
            : 'TIDAK ADA DATA';

        $deviceName = $device->name ?? 'ESP-' . $device->id;
        $deviceLocation = $device->location ?? 'Tidak diketahui';

        // 🔥 CEK LEVEL ASAP SAAT OFFLINE
        $smokeLevel = $device->smoke_value ?? 0;
        $smokeStatus = '';
        if ($smokeLevel >= 500) {
            $smokeStatus = '🔴 DANGER (Asap Tinggi!)';
        } elseif ($smokeLevel >= 200) {
            $smokeStatus = '🟡 WARNING (Asap Terdeteksi!)';
        } else {
            $smokeStatus = '🟢 NORMAL';
        }

        $message = "⚠️ ESP OFFLINE!

📡 Nama Device : {$deviceName}
📍 Lokasi : {$deviceLocation}
⏱️ Terakhir kirim: {$lastTime}
⌛ Sudah: {$minutesDiff} MENIT tidak ada data!

📊 Data Terakhir:
├─ Asap: {$lastSmoke} ppm
├─ Status Asap: {$smokeStatus}
└─ Status Device: {$lastStatus}

🔍 TINDAKAN YANG HARUS DILAKUKAN:
================================
1️⃣ 🔌 CEK ESP
   - Apakah ESP menyala?
   - Cek LED indikator power
   - Reset ESP (tekan tombol reset)

2️⃣ 📶 CEK WIFI
   - Apakah ESP terhubung ke WiFi?
   - Cek sinyal WiFi di lokasi ESP
   - Restart router jika perlu

3️⃣ 🌐 CEK INTERNET & LISTRIK
   - Apakah internet berjalan normal?
   - Cek listrik di lokasi ESP
   - Cek router/modem menyala?

4️⃣ 🔑 KEMUNGKINAN PERGANTIAN PASSWORD WIFI
   - Apakah baru ganti password WiFi?
   - Update password WiFi di ESP
   - Rekonfigurasi koneksi WiFi ESP

🕐 " . Carbon::now()->format('d-m-Y H:i:s');

        // Kirim ke semua kontak aktif
        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA ESP alert dikirim ke: {$contact->phone} - {$deviceName}");
            } else {
                Log::error("❌ Gagal kirim WA ESP alert ke: {$contact->phone}");
            }
        }

        // 🔥 SIMPAN LOG ALERT (untuk tracking spam)
        try {
            SmokeLog::create([
                'device_id' => $device->id,
                'smoke_value' => $device->smoke_value ?? 0,
                'status' => 'OFFLINE',
                'message' => "🚨 ALERT: ESP OFFLINE selama {$minutesDiff} menit",
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Gagal simpan log alert: " . $e->getMessage());
        }
    }

    /**
     * GET STATUS ESP (untuk command atau debugging)
     */
    public function getEspStatus()
    {
        $devices = SmokeDevice::all();
        $result = [];

        if ($devices->isEmpty()) {
            return [
                'status' => 'NO_DEVICE',
                'message' => 'Tidak ada device ESP yang terdaftar',
                'data' => []
            ];
        }

        foreach ($devices as $device) {
            $isOnline = $device->last_seen_at &&
                        Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

            $lastLog = SmokeLog::where('device_id', $device->id)
                ->orderBy('created_at', 'desc')
                ->first();

            // Tentukan status asap
            $smokeValue = $device->smoke_value ?? 0;
            if ($smokeValue >= 500) {
                $smokeStatus = 'DANGER';
            } elseif ($smokeValue >= 200) {
                $smokeStatus = 'WARNING';
            } else {
                $smokeStatus = 'NORMAL';
            }

            $result[] = [
                'id' => $device->id,
                'name' => $device->name ?? 'ESP-' . $device->id,
                'location' => $device->location ?? 'Tidak diketahui',
                'is_online' => $isOnline,
                'device_status' => $isOnline ? 'ONLINE' : 'OFFLINE',
                'smoke_value' => $smokeValue,
                'smoke_status' => $smokeStatus,
                'last_seen' => $device->last_seen_at
                    ? Carbon::parse($device->last_seen_at)->format('d-m-Y H:i:s')
                    : 'Never',
                'minutes_ago' => $device->last_seen_at
                    ? Carbon::parse($device->last_seen_at)->diffInMinutes(now())
                    : null,
                'last_smoke' => $lastLog?->smoke_value ?? 'N/A',
                'status' => $lastLog?->status ?? 'N/A'
            ];
        }

        return [
            'status' => 'OK',
            'total_devices' => count($result),
            'online_count' => collect($result)->where('is_online', true)->count(),
            'offline_count' => collect($result)->where('is_online', false)->count(),
            'data' => $result
        ];
    }

    /**
     * CEK SATU DEVICE SECARA SPESIFIK
     */
    public function checkDevice($deviceId)
    {
        $device = SmokeDevice::find($deviceId);

        if (!$device) {
            return [
                'success' => false,
                'message' => 'Device tidak ditemukan'
            ];
        }

        $isOnline = $device->last_seen_at &&
                    Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

        // Update status
        $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
        $device->save();

        return [
            'success' => true,
            'device' => [
                'id' => $device->id,
                'name' => $device->name,
                'location' => $device->location,
                'is_online' => $isOnline,
                'device_status' => $device->device_status,
                'smoke_value' => $device->smoke_value,
                'last_seen_at' => $device->last_seen_at?->format('d-m-Y H:i:s')
            ]
        ];
    }
}