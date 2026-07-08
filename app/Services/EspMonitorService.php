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
    // 🔥 KONFIGURASI THRESHOLD
    private const WARNING_THRESHOLD = 200;
    private const DANGER_THRESHOLD = 500;

    /**
     * CEK STATUS ESP - Apakah mengirim data dalam 2 menit terakhir
     */
    public function checkEspStatus()
    {
        $devices = SmokeDevice::all();

        if ($devices->isEmpty()) {
            Log::info('📡 Tidak ada device ESP yang terdaftar');
            return;
        }

        foreach ($devices as $device) {
            // Cek apakah device online (kirim data dalam 2 menit terakhir)
            $isOnline = $device->last_seen_at &&
                        Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

            // 🔥 UPDATE STATUS DI DATABASE
            $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
            $device->save();

            // 🔥 CEK SMOKE LEVEL (TERPISAH DARI OFFLINE)
            $this->checkSmokeLevel($device);

            // Jika OFFLINE (tidak kirim data > 2 menit)
            if (!$isOnline) {
                $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $lastSeen = $device->last_seen_at
                    ? Carbon::parse($device->last_seen_at)
                    : null;

                $minutesDiff = $lastSeen
                    ? $lastSeen->diffInMinutes(now())
                    : 'TIDAK ADA DATA';

                // CEK APAKAH SUDAH PERNAH KIRIM ALERT SEBELUMNYA
                $lastAlertLog = SmokeLog::where('smoke_device_id', $device->id)
                    ->where('message', 'LIKE', '%OFFLINE%')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $shouldSendAlert = true;
                if ($lastAlertLog) {
                    $lastAlertTime = Carbon::parse($lastAlertLog->created_at);
                    $minutesSinceLastAlert = $lastAlertTime->diffInMinutes(now());
                    
                    if ($minutesSinceLastAlert < 30) {
                        $shouldSendAlert = false;
                        Log::info("⏳ Skip offline alert untuk {$device->name}, terakhir kirim {$minutesSinceLastAlert} menit lalu");
                    }
                }

                if ($shouldSendAlert) {
                    $this->sendEspOfflineAlert($device, $minutesDiff);
                }
            }
        }

        Log::info('✅ ESP monitoring selesai, total device: ' . $devices->count());
    }

    /**
     * 🔥 CEK SMOKE LEVEL DAN KIRIM WA JIKA MELEBIHI THRESHOLD
     */
    private function checkSmokeLevel($device)
    {
        $smokeValue = $device->smoke_value ?? 0;
        
        // Tentukan status
        if ($smokeValue >= self::DANGER_THRESHOLD) {
            $status = 'DANGER';
            $message = "Asap TINGGI! {$smokeValue} ppm - Segera periksa!";
        } elseif ($smokeValue >= self::WARNING_THRESHOLD) {
            $status = 'WARNING';
            $message = "Asap terdeteksi! {$smokeValue} ppm - Waspada!";
        } else {
            $status = 'NORMAL';
            $message = "Kondisi aman ({$smokeValue} ppm)";
        }

        // 🔥 SIMPAN STATUS SEBELUMNYA (untuk cek perubahan)
        $oldStatus = $device->status ?? 'NORMAL';

        // 🔥 UPDATE DEVICE
        $device->status = $status;
        $device->save();

        // 🔥 CEK APAKAH STATUS BERUBAH
        if ($oldStatus !== $status) {
            // Simpan log
            SmokeLog::create([
                'smoke_device_id' => $device->id,
                'smoke_value' => $smokeValue,
                'status' => $status,
                'message' => $message,
            ]);

            Log::info("📝 Smoke log: {$device->name} - {$oldStatus} → {$status} ({$smokeValue} ppm)");

            // 🔥 KIRIM WA JIKA STATUS BERUBAH KE WARNING ATAU DANGER
            if ($status === 'WARNING' || $status === 'DANGER') {
                $this->sendSmokeAlert($device, $smokeValue, $status);
            }
            
            // 🔥 KIRIM WA JIKA STATUS KEMBALI NORMAL
            if ($status === 'NORMAL' && ($oldStatus === 'WARNING' || $oldStatus === 'DANGER')) {
                $this->sendSmokeNormalAlert($device, $smokeValue);
            }
        } else {
            // 🔥 STATUS SAMA → UPDATE WAKTU LOG TERAKHIR
            $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastLog) {
                $lastLog->update([
                    'smoke_value' => $smokeValue,
                    'message' => $message,
                    'updated_at' => Carbon::now(),
                ]);
                Log::info("⏱️ Update smoke log: {$device->name} - {$status} ({$smokeValue} ppm)");
            }
        }
    }

    /**
     * 🔥 KIRIM WA ALERT SMOKE (WARNING/DANGER)
     */
    private function sendSmokeAlert($device, $smokeValue, $status)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA smoke alert');
            return;
        }

        $deviceStatus = $device->device_status ?? 'UNKNOWN';

        if ($status === 'DANGER') {
            $icon = '🔴';
            $title = '🚨 DANGER! ASAP TINGGI!';
            $tindakan = "1️⃣ 🏃 SEGERA EVAKUASI!\n2️⃣ 🔥 Matikan sumber api / listrik\n3️⃣ 🚒 Hubungi petugas pemadam\n4️⃣ 🚪 Buka ventilasi / pintu";
        } else {
            $icon = '🟡';
            $title = '⚠️ PERINGATAN ASAP!';
            $tindakan = "1️⃣ 🔍 Periksa sumber asap\n2️⃣ 💨 Buka ventilasi / jendela\n3️⃣ 🧯 Siapkan APAR jika diperlukan\n4️⃣ 📱 Pantau terus kondisi asap";
        }

        $message = "{$icon} {$title}

📊 {$smokeValue} ppm
📟 {$deviceStatus}

⚠️ Status : {$status}

🔍 TINDAKAN:
{$tindakan}

🕐 " . Carbon::now()->format('d-m-Y H:i:s');

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA smoke alert dikirim ke: {$contact->phone} - {$status}");
            } else {
                Log::error("❌ Gagal kirim WA smoke alert ke: {$contact->phone}");
            }
        }
    }

    /**
     * 🔥 KIRIM WA KETIKA SMOKE KEMBALI NORMAL
     */
    private function sendSmokeNormalAlert($device, $smokeValue)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA smoke normal alert');
            return;
        }

        $message = "🟢 SMOKE NORMAL

📊 {$smokeValue} ppm

✅ Status : NORMAL (Aman)

💡 Kondisi asap telah kembali normal.

🕐 " . Carbon::now()->format('d-m-Y H:i:s');

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA smoke normal alert dikirim ke: {$contact->phone}");
            } else {
                Log::error("❌ Gagal kirim WA smoke normal alert ke: {$contact->phone}");
            }
        }
    }

    /**
     * 🔥 KIRIM ALERT ESP OFFLINE - PESAN SINGKAT
     */
    private function sendEspOfflineAlert($device, $minutesDiff)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA ESP alert');
            return;
        }

        $message = "⚠️ ESP OFFLINE!

📡 ESP tidak mengirim data selama {$minutesDiff} menit.

🔍 Cek:
1️⃣ Power ESP
2️⃣ Koneksi WiFi
3️⃣ Internet & listrik

🕐 " . Carbon::now()->format('d-m-Y H:i:s');

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA ESP offline alert dikirim ke: {$contact->phone}");
            } else {
                Log::error("❌ Gagal kirim WA ESP offline alert ke: {$contact->phone}");
            }
        }

        // 🔥 SIMPAN LOG ALERT
        try {
            SmokeLog::create([
                'smoke_device_id' => $device->id,
                'smoke_value' => $device->smoke_value ?? 0,
                'status' => 'OFFLINE',
                'message' => "🚨 ALERT: ESP OFFLINE selama {$minutesDiff} menit",
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Gagal simpan log alert: " . $e->getMessage());
        }
    }

    /**
     * GET STATUS ESP (untuk debugging)
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

            $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $smokeValue = $device->smoke_value ?? 0;
            if ($smokeValue >= self::DANGER_THRESHOLD) {
                $smokeStatus = 'DANGER';
            } elseif ($smokeValue >= self::WARNING_THRESHOLD) {
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