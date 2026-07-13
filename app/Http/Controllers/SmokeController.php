<?php

namespace App\Http\Controllers;

use App\Models\SmokeDevice;
use App\Models\SmokeLog;
use App\Models\Contact;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmokeController extends Controller
{
    /**
     * ============================================================
     *  🔥 KONFIGURASI THRESHOLD PPM
     * ============================================================
     */
    private const WARNING_THRESHOLD = 700;
    private const DANGER_THRESHOLD  = 1000;
    private const DEFAULT_DEVICE_NAME = 'ESP32-Smoke';
    private const DEFAULT_DEVICE_LOCATION = 'Ruang Server';

    /**
     * ============================================================
     *  📊 DISPLAY SMOKE MONITORING (WEB)
     * ============================================================
     */
    public function index(Request $request)
    {
        // ==================== AMBIL SEMUA DEVICE ====================
        $devices = SmokeDevice::all();

        // ==================== UPDATE STATUS ONLINE/OFFLINE ====================
        foreach ($devices as $device) {
            $isOnline = $device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;
            $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
            $device->save();
        }

        // ==================== HITUNG STATUS & STATISTIK ====================
        $totalDevice = SmokeDevice::count();
        $online = SmokeDevice::where('device_status', 'ONLINE')->count();
        $offline = SmokeDevice::where('device_status', 'OFFLINE')->count();
        
        $normal = 0;
        $warning = 0;
        $danger = 0;

        foreach ($devices as $device) {
            $ppm = $device->smoke_value ?? 0;
            
            if ($ppm >= self::DANGER_THRESHOLD) {
                $danger++;
                $device->status = 'DANGER';
                $device->status_label = '🔴 DANGER';
                $device->status_class = 'danger';
                $device->status_icon = '🔥';
            } elseif ($ppm >= self::WARNING_THRESHOLD) {
                $warning++;
                $device->status = 'WARNING';
                $device->status_label = '🟡 WARNING';
                $device->status_class = 'warning';
                $device->status_icon = '⚠️';
            } else {
                $normal++;
                $device->status = 'NORMAL';
                $device->status_label = '🟢 NORMAL';
                $device->status_class = 'normal';
                $device->status_icon = '✅';
            }
        }

        // ==================== CHART LOGS ====================
        $chartLogs = SmokeLog::latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        // ==================== LOGS UNTUK TABEL ====================
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        
        // 🔥 AMBIL SEMUA LOG, FILTER HANYA NORMAL, WARNING, DANGER
        $allLogs = SmokeLog::with('device')
            ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // 🔥 FILTER: HANYA TAMPILKAN LOG YANG STATUSNYA BERUBAH
        $filteredLogs = [];
        $lastStatus = null;
        
        foreach ($allLogs as $log) {
            if ($lastStatus === null || $log->status !== $lastStatus) {
                $filteredLogs[] = $log;
                $lastStatus = $log->status;
            }
        }
        
        // 🔥🔥🔥 HITUNG TOTAL DATA YANG DIFILTER 🔥🔥🔥
        $totalFiltered = count($filteredLogs);
        
        // Paginasi manual
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = array_slice($filteredLogs, $offset, $perPage);
        
        // 🔥🔥🔥 BUAT PAGINATOR DENGAN DATA YANG SUDAH DIFILTER 🔥🔥🔥
        $smokeLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            $totalFiltered,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // 🔥🔥🔥 SET COLLECTION DENGAN DATA YANG SUDAH DIPAGINASI 🔥🔥🔥
        $smokeLogs->setCollection(collect($paginatedLogs));

        $onlineCount = $online;

        return view('smoke', compact(
            'devices',
            'totalDevice',
            'online',
            'offline',
            'normal',
            'warning',
            'danger',
            'onlineCount',
            'chartLogs',
            'smokeLogs',
            'perPage'
        ));
    }

    /**
     * ============================================================
     *  📡 ESP32 KIRIM DATA (POST /api/smoke)
     * ============================================================
     */
    public function receiveData(Request $request)
    {
        try {
            $validated = $request->validate([
                'ppm' => 'required|integer|min:0|max:10000',
            ]);

            $ppm = $validated['ppm'];

            // CARI ATAU BUAT DEVICE
            $device = SmokeDevice::first();
            if (!$device) {
                $device = SmokeDevice::create([
                    'name' => self::DEFAULT_DEVICE_NAME,
                    'location' => self::DEFAULT_DEVICE_LOCATION,
                    'device_status' => 'ONLINE',
                    'smoke_value' => $ppm,
                    'status' => 'NORMAL',
                    'last_seen_at' => Carbon::now(),
                    'last_wa_sent_at' => null,
                ]);
            }

            // 🔥 CEK APAKAH SEBELUMNYA OFFLINE
            $wasOffline = ($device->device_status === 'OFFLINE');

            // STATUS LAMA & PPM LAMA
            $oldStatus = $device->status;
            $oldPpm = $device->smoke_value ?? 0;

            // TENTUKAN STATUS
            if ($ppm >= self::DANGER_THRESHOLD) {
                $status = 'DANGER';
                $message = "🔥 Asap tinggi!  - Segera periksa!";
                $icon = '🔥';
            } elseif ($ppm >= self::WARNING_THRESHOLD) {
                $status = 'WARNING';
                $message = "⚠️ Asap terdeteksi! - Waspada!";
                $icon = '⚠️';
            } else {
                $status = 'NORMAL';
                $message = "✅ Kondisi aman";
                $icon = '✅';
            }

            // ============================================================
            // 🔥 LOGIKA UTAMA: 
            // 1. HANYA SAVE LOG JIKA STATUS BERUBAH
            // 2. UPDATE PPM DI LOG TERAKHIR JIKA STATUS SAMA
            // ============================================================
            $isStatusChanged = ($oldStatus != $status);
            $log = null;
            $isNewLogSaved = false;
            $isPpmUpdated = false;
            $lastLog = null;
            $updatedLog = null;

            if ($isStatusChanged) {
                // 🔥 STATUS BERUBAH → SAVE LOG BARU
                $log = SmokeLog::create([
                    'smoke_device_id' => $device->id,
                    'smoke_value' => $ppm,
                    'status' => $status,
                    'message' => $message,
                ]);
                $isNewLogSaved = true;
                $updatedLog = $log;
                
                Log::info("📝 Log baru tersimpan: Status berubah dari {$oldStatus} ke {$status} ({$ppm} ppm)");
            } else {
                // 🔥 STATUS SAMA → UPDATE PPM DAN WAKTU LOG TERAKHIR
                $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                    ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastLog) {
                    // Update nilai PPM dan waktu
                    $lastLog->smoke_value = $ppm;
                    $lastLog->updated_at = Carbon::now();
                    $lastLog->save();
                    $isPpmUpdated = true;
                    $updatedLog = $lastLog;
                    
                    Log::info("🔄 Log diupdate: Status {$status} tetap, PPM diupdate dari {$oldPpm} ke {$ppm}");
                } else {
                    // Jika tidak ada log sama sekali (first time)
                    $log = SmokeLog::create([
                        'smoke_device_id' => $device->id,
                        'smoke_value' => $ppm,
                        'status' => $status,
                        'message' => $message,
                    ]);
                    $isNewLogSaved = true;
                    $updatedLog = $log;
                    Log::info("📝 Log pertama tersimpan: {$status} ({$ppm} ppm)");
                }
            }

            // UPDATE DEVICE (SELALU UPDATE)
            $device->update([
                'smoke_value' => $ppm,
                'status' => $status,
                'device_status' => 'ONLINE',
                'last_seen_at' => Carbon::now(),
            ]);

            // 🔥🔥🔥 KIRIM WA JIKA SEBELUMNYA OFFLINE (ESP KEMBALI ONLINE) 🔥🔥🔥
            if ($wasOffline) {
                Log::info("📱 ESP kembali online, kirim WA online alert");
                $this->sendEspOnlineAlert($device);
            }

            // ============================================================
            // 🔥🔥🔥 KIRIM WA HANYA KETIKA STATUS NAIK 🔥🔥🔥
            // ============================================================
            $shouldSendWA = false;
            $waReason = '';

            // 🔥 HANYA PROSES JIKA STATUS BERUBAH
            if ($isStatusChanged) {
                // 🔥 CEK APAKAH STATUS NAIK (MEMBURUK)
                $isStatusUp = false;
                
                // CEK STATUS NAIK
                if ($oldStatus == 'NORMAL' && in_array($status, ['WARNING', 'DANGER'])) {
                    $isStatusUp = true; // NORMAL → WARNING/DANGER
                } elseif ($oldStatus == 'WARNING' && $status == 'DANGER') {
                    $isStatusUp = true; // WARNING → DANGER
                }
                // SELAIN ITU (DANGER→WARNING, WARNING→NORMAL, DANGER→NORMAL) = TURUN
                
                if ($isStatusUp) {
                    // 🔥 KIRIM WA KETIKA STATUS NAIK:
                    // 1. NORMAL → WARNING  ✅ KIRIM
                    // 2. NORMAL → DANGER   ✅ KIRIM
                    // 3. WARNING → DANGER  ✅ KIRIM
                    $shouldSendWA = true;
                    $waReason = 'Status naik dari ' . $oldStatus . ' ke ' . $status;
                    
                    Log::info("📱 WA akan dikirim (status naik): {$oldStatus} → {$status} ({$ppm} ppm)");
                } else {
                    // Status turun (DANGER→WARNING, WARNING→NORMAL, DANGER→NORMAL)
                    Log::info("⏭️ WA tidak dikirim (status turun): {$oldStatus} → {$status} ({$ppm} ppm)");
                }
            } else {
                // STATUS SAMA → TIDAK KIRIM WA (walaupun PPM berubah)
                Log::info("⏭️ WA tidak dikirim: Status {$status} tetap (PPM diupdate dari {$oldPpm} ke {$ppm})");
            }

            // KIRIM WA JIKA MEMENUHI SYARAT
            if ($shouldSendWA) {
                $this->sendSmokeAlert($device, $ppm, $status);
                
                $device->update([
                    'last_wa_sent_at' => Carbon::now(),
                ]);
                
                Log::info("📱 WA berhasil dikirim: {$waReason} - {$status} ({$ppm} ppm)");
            }

            // ============================================================
            // 📊 RESPONSE
            // ============================================================
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses',
                'data' => [
                    'ppm' => $ppm,
                    'old_ppm' => $oldPpm,
                    'status' => $status,
                    'old_status' => $oldStatus,
                    'status_label' => $this->getStatusLabel($status),
                    'status_class' => $this->getStatusClass($status),
                    'icon' => $icon,
                    'log_id' => $log?->id ?? $lastLog?->id,
                    'is_status_changed' => $isStatusChanged,
                    'is_new_log_saved' => $isNewLogSaved,
                    'is_ppm_updated' => $isPpmUpdated,
                    'device_name' => $device->name,
                    'was_offline' => $wasOffline,
                    'created_at' => $log?->created_at?->format('Y-m-d H:i:s') ?? $lastLog?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'updated_at' => $lastLog?->updated_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'wa_sent' => $shouldSendWA,
                    'wa_reason' => $waReason,
                    'latest_log' => $updatedLog ? [
                        'id' => $updatedLog->id,
                        'ppm' => $updatedLog->smoke_value,
                        'status' => $updatedLog->status,
                        'status_label' => $this->getStatusLabel($updatedLog->status),
                        'status_class' => $this->getStatusClass($updatedLog->status),
                        'message' => $updatedLog->message,
                        'created_at' => $updatedLog->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $updatedLog->updated_at->format('Y-m-d H:i:s'),
                    ] : null,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("❌ Error ESP32: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔍 CEK STATUS SEMUA ESP (ONLINE/OFFLINE)
     * ============================================================
     */
    public function checkEspStatus()
    {
        try {
            Log::info('🔄 ===== START CHECK ESP STATUS =====');
            
            $devices = SmokeDevice::all();

            if ($devices->isEmpty()) {
                Log::info('📡 Tidak ada device ESP yang terdaftar');
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada device ESP yang terdaftar',
                    'data' => []
                ]);
            }

            $results = [];
            
            foreach ($devices as $device) {
                // Cek online/offline (2 menit terakhir)
                $isOnline = $device->last_seen_at &&
                            Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;
                
                // Simpan status lama
                $oldDeviceStatus = $device->device_status;
                
                // Update status device
                $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
                $device->save();
                
                // 🔥🔥🔥 LANGSUNG KIRIM WA KETIKA OFFLINE 🔥🔥🔥
                if (!$isOnline) {
                    Log::info("🚨🚨🚨 Device {$device->name} OFFLINE!");
                    Log::info("   - oldDeviceStatus: {$oldDeviceStatus}");
                    Log::info("   - smoke_value: {$device->smoke_value}");
                    
                    $lastSeen = Carbon::parse($device->last_seen_at);
                    $minutesDiff = $lastSeen->diffInMinutes(now());
                    Log::info("   - Durasi offline: {$minutesDiff} menit");
                    
                    Log::info("📤📤📤 MENGIRIM WA ESP OFFLINE...");
                    $this->sendEspOfflineAlert($device, $minutesDiff);
                } else {
                    Log::info("✅ Device {$device->name} ONLINE");
                    
                    if ($oldDeviceStatus === 'OFFLINE') {
                        Log::info("🟢🟢🟢 Device {$device->name} ONLINE! (sebelumnya OFFLINE)");
                        $this->sendEspOnlineAlert($device);
                    }
                }
                
                $results[] = [
                    'id' => $device->id,
                    'name' => $device->name,
                    'location' => $device->location,
                    'is_online' => $isOnline,
                    'device_status' => $device->device_status,
                    'smoke_value' => $device->smoke_value,
                    'last_seen_at' => $device->last_seen_at?->format('Y-m-d H:i:s'),
                ];
            }
            
            Log::info('✅ ===== ESP MONITORING SELESAI =====');
            Log::info('📊 Stats: Total=' . $devices->count() . ', Online=' . $devices->where('device_status', 'ONLINE')->count() . ', Offline=' . $devices->where('device_status', 'OFFLINE')->count());
            
            return response()->json([
                'success' => true,
                'message' => 'Monitoring ESP selesai',
                'data' => $results,
                'stats' => [
                    'total' => $devices->count(),
                    'online' => $devices->where('device_status', 'ONLINE')->count(),
                    'offline' => $devices->where('device_status', 'OFFLINE')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error check ESP status: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal monitoring ESP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔥 KIRIM WA ESP OFFLINE
     * ============================================================
     */
    private function sendEspOfflineAlert($device, $minutesDiff)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA ESP offline alert');
            return;
        }

        $message = "⚠️ ESP OFFLINE!

📡 ESP tidak mengirim data selama {$minutesDiff} menit.
📍 Lokasi: {$device->location}
📊 Status terakhir: {$device->smoke_value} ppm

🔍 TINDAKAN YANG HARUS DILAKUKAN:
1️⃣ Cek power / sumber listrik ESP
2️⃣ Cek koneksi WiFi ESP
3️⃣ Cek koneksi internet router
4️⃣ Restart / reboot ESP32
5️⃣ Pastikan ESP dalam jangkauan WiFi

🕐 " . Carbon::now()->format('d-m-Y H:i:s');

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA ESP offline alert dikirim ke: {$contact->phone}");
            } else {
                Log::error("❌ Gagal kirim WA ESP offline alert ke: {$contact->phone}");
            }
        }
    }

    /**
     * ============================================================
     *  🟢 KIRIM WA ESP ONLINE
     * ============================================================
     */
    private function sendEspOnlineAlert($device)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA ESP online alert');
            return;
        }

        $message = "🟢 ESP ONLINE!

📡 ESP kembali mengirim data.
📍 Lokasi: {$device->location}
📊 Smoke Value: {$device->smoke_value} ppm
📟 Status: {$device->status}

✅ ESP telah kembali online dan berfungsi normal.

🕐 " . Carbon::now()->format('d-m-Y H:i:s');

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA ESP online alert dikirim ke: {$contact->phone}");
            } else {
                Log::error("❌ Gagal kirim WA ESP online alert ke: {$contact->phone}");
            }
        }
    }

    /**
     * ============================================================
     *  🔍 CEK SATU DEVICE
     * ============================================================
     */
    public function checkSingleDevice($id)
    {
        try {
            $device = SmokeDevice::find($id);

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device tidak ditemukan'
                ], 404);
            }

            $isOnline = $device->last_seen_at &&
                        Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

            $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
            $device->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $device->id,
                    'name' => $device->name,
                    'location' => $device->location,
                    'is_online' => $isOnline,
                    'device_status' => $device->device_status,
                    'smoke_value' => $device->smoke_value,
                    'status' => $device->status,
                    'last_seen_at' => $device->last_seen_at?->format('d-m-Y H:i:s'),
                    'last_seen_human' => $device->last_seen_at?->diffForHumans(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error check device: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal cek device: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔥 KIRIM WHATSAPP ALERT
     * ============================================================
     */
    private function sendSmokeAlert($device, $ppm, $status)
    {
        if (!in_array($status, ['WARNING', 'DANGER'])) {
            Log::info("⏭️ WA tidak dikirim: Status {$status} (hanya WARNING/DANGER)");
            return;
        }

        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA smoke alert');
            return;
        }

        if ($status == 'DANGER') {
            $message = 
"🔴 DANGER! ASAP TINGGI!

📊 Nilai Asap : {$ppm} ppm
⚠️ Status    : DANGER
📍 Lokasi    : {$device->location}

🔍 TINDAKAN:
1️⃣  SEGERA EVAKUASI!
2️⃣  Matikan sumber api / listrik
3️⃣  Hubungi petugas pemadam
4️⃣  Buka ventilasi / pintu

🕐 " . now()->format('d-m-Y H:i:s');
        } else {
            $message = 
"🟡 PERINGATAN ASAP!

📊 Nilai Asap : {$ppm} ppm
⚠️ Status    : WARNING
📍 Lokasi    : {$device->location}

🔍 TINDAKAN:
1️⃣  Periksa sumber asap
2️⃣  Buka ventilasi / jendela
3️⃣  Siapkan APAR
4️⃣  Pantau terus kondisi asap

🕐 " . now()->format('d-m-Y H:i:s');
        }

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
     * ============================================================
     *  📊 GET STATUS TERBARU (GET /api/smoke/status)
     * ============================================================
     */
    public function getStatus()
    {
        try {
            $device = SmokeDevice::first();
            
            if (!$device) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'ppm' => 0,
                        'status' => 'NORMAL',
                        'status_label' => '🟢 NORMAL',
                        'status_class' => 'normal',
                        'device_status' => 'OFFLINE',
                        'last_seen_at' => null,
                        'is_status_changed' => false,
                        'is_ppm_updated' => false,
                        'latest_log' => null,
                    ]
                ]);
            }

            $ppm = $device->smoke_value ?? 0;
            
            if ($ppm >= self::DANGER_THRESHOLD) {
                $status = 'DANGER';
                $label = '🔴 DANGER';
                $class = 'danger';
            } elseif ($ppm >= self::WARNING_THRESHOLD) {
                $status = 'WARNING';
                $label = '🟡 WARNING';
                $class = 'warning';
            } else {
                $status = 'NORMAL';
                $label = '🟢 NORMAL';
                $class = 'normal';
            }

            $isOnline = $device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

            // 🔥 AMBIL LOG TERAKHIR
            $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            $isStatusChanged = false;
            $isPpmUpdated = false;
            
            if ($lastLog) {
                if ($lastLog->status != $status) {
                    $isStatusChanged = true;
                }
                elseif ($lastLog->status == $status && $lastLog->smoke_value != $ppm) {
                    $isPpmUpdated = true;
                }
            }

            $latestLogData = null;
            if ($lastLog) {
                $latestLogData = [
                    'id' => $lastLog->id,
                    'ppm' => $lastLog->smoke_value,
                    'status' => $lastLog->status,
                    'status_label' => $this->getStatusLabel($lastLog->status),
                    'status_class' => $this->getStatusClass($lastLog->status),
                    'message' => $lastLog->message ?? '',
                    'created_at' => $lastLog->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $lastLog->updated_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'ppm' => $ppm,
                    'status' => $status,
                    'status_label' => $label,
                    'status_class' => $class,
                    'device_status' => $isOnline ? 'ONLINE' : 'OFFLINE',
                    'last_seen_at' => $device->last_seen_at?->format('Y-m-d H:i:s'),
                    'last_seen_human' => $device->last_seen_at?->diffForHumans(),
                    'is_status_changed' => $isStatusChanged,
                    'is_ppm_updated' => $isPpmUpdated,
                    'last_log_status' => $lastLog?->status,
                    'last_log_ppm' => $lastLog?->smoke_value,
                    'latest_log' => $latestLogData,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error get status: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📜 GET LOGS HISTORY
     * ============================================================
     */
    public function getLogs(Request $request)
    {
        try {
            $limit = $request->input('limit', 50);
            
            $allLogs = SmokeLog::with('device')
                ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
                ->orderBy('created_at', 'desc')
                ->take($limit * 2)
                ->get();
            
            $filteredLogs = [];
            $lastStatus = null;
            
            foreach ($allLogs as $log) {
                if ($lastStatus === null || $log->status !== $lastStatus) {
                    $filteredLogs[] = $log;
                    $lastStatus = $log->status;
                }
            }
            
            $filteredLogs = array_slice($filteredLogs, 0, $limit);
            
            $logs = collect($filteredLogs)->map(function($log) {
                return [
                    'id' => $log->id,
                    'ppm' => $log->smoke_value,
                    'status' => $log->status,
                    'status_label' => $this->getStatusLabel($log->status),
                    'status_class' => $this->getStatusClass($log->status),
                    'message' => $log->message,
                    'device_name' => $log->device->name ?? 'Unknown',
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $log->created_at->diffForHumans(),
                    'updated_at' => $log->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $logs,
                'total' => $logs->count(),
                'filtered' => true,
                'message' => 'Menampilkan log perubahan status (NORMAL, WARNING, DANGER)',
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error get logs: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📊 API: Ambil data smoke terbaru
     * ============================================================
     */
    public function getLatestData()
    {
        try {
            $logs = SmokeLog::latest()
                ->take(20)
                ->get()
                ->reverse()
                ->values()
                ->map(function($log) {
                    return [
                        'time' => $log->created_at->format('H:i:s'),
                        'value' => $log->smoke_value,
                        'status' => $log->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error get latest data: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data terbaru: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📊 API: Ambil status terbaru semua device
     * ============================================================
     */
    public function getDeviceStatus()
    {
        try {
            $devices = SmokeDevice::all()->map(function($device) {
                $ppm = $device->smoke_value ?? 0;
                
                if ($ppm >= self::DANGER_THRESHOLD) {
                    $status = 'DANGER';
                    $label = '🔴 DANGER';
                    $class = 'danger';
                } elseif ($ppm >= self::WARNING_THRESHOLD) {
                    $status = 'WARNING';
                    $label = '🟡 WARNING';
                    $class = 'warning';
                } else {
                    $status = 'NORMAL';
                    $label = '🟢 NORMAL';
                    $class = 'normal';
                }

                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'location' => $device->location,
                    'smoke_value' => $ppm,
                    'status' => $status,
                    'status_label' => $label,
                    'status_class' => $class,
                    'device_status' => $device->device_status,
                    'last_seen_at' => $device->last_seen_at?->format('d/m/Y H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'devices' => $devices,
                'total' => $devices->count(),
                'online' => $devices->where('device_status', 'ONLINE')->count(),
                'offline' => $devices->where('device_status', 'OFFLINE')->count(),
            ]);

        } catch (\Exception $e) {
            Log::error("❌ Error get device status: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status device: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  🔥 UPDATE THRESHOLD
     * ============================================================
     */
    public function updateThreshold(Request $request)
    {
        try {
            $request->validate([
                'warning' => 'required|integer|min:0',
                'danger' => 'required|integer|min:0|gt:warning',
            ]);

            config(['smoke.thresholds.warning' => $request->warning]);
            config(['smoke.thresholds.danger' => $request->danger]);

            return response()->json([
                'success' => true,
                'message' => 'Threshold berhasil diupdate',
                'thresholds' => [
                    'warning' => $request->warning,
                    'danger' => $request->danger,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("❌ Error update threshold: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal update threshold: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📥 EXPORT LOGS KE CSV
     * ============================================================
     */
    public function export(Request $request)
    {
        try {
            $allLogs = SmokeLog::with('device')
                ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
                ->orderBy('created_at', 'asc')
                ->get();

            $filteredLogs = [];
            $lastStatus = null;
            
            foreach ($allLogs as $log) {
                if ($lastStatus === null || $log->status !== $lastStatus) {
                    $filteredLogs[] = $log;
                    $lastStatus = $log->status;
                }
            }

            if (empty($filteredLogs)) {
                return redirect()
                    ->back()
                    ->with('warning', 'Tidak ada data untuk diexport');
            }

            $filename = 'smoke_logs_status_changes_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($filteredLogs) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");

                fputcsv($file, [
                    'No',
                    'Tanggal & Waktu',
                    'Device',
                    'Nilai Asap (ppm)',
                    'Status',
                    'Durasi Status (menit)',
                    'Keterangan'
                ]);

                $no = 1;
                $totalLogs = count($filteredLogs);
                
                foreach ($filteredLogs as $index => $log) {
                    $statusLabel = $log->status ?? 'NORMAL';
                    $statusIcon = match($statusLabel) {
                        'DANGER' => '🔴 DANGER',
                        'WARNING' => '🟡 WARNING',
                        default => '🟢 NORMAL',
                    };

                    $message = $log->message ?? match($statusLabel) {
                        'DANGER' => '🔥 Asap tinggi! Periksa segera!',
                        'WARNING' => '⚠️ Asap mulai terdeteksi, waspada!',
                        default => '✅ Kondisi aman, tidak ada asap',
                    };

                    $duration = '-';
                    if ($index < $totalLogs - 1) {
                        $nextLog = $filteredLogs[$index + 1];
                        $diffMinutes = Carbon::parse($log->created_at)->diffInMinutes(Carbon::parse($nextLog->created_at));
                        $duration = $diffMinutes . ' menit';
                    }

                    fputcsv($file, [
                        $no++,
                        $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                        $log->device->name ?? 'Unknown Device',
                        number_format($log->smoke_value ?? 0, 0),
                        $statusIcon,
                        $duration,
                        $message,
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error("❌ Error export CSV: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * ============================================================
     *  📊 Helper: GET STATUS LABEL
     * ============================================================
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'DANGER' => '🔴 DANGER',
            'WARNING' => '🟡 WARNING',
            'NORMAL' => '🟢 NORMAL',
            'OFFLINE' => '⚫ OFFLINE',
        ];
        return $labels[$status] ?? '🟢 NORMAL';
    }

    /**
     * ============================================================
     *  📊 Helper: GET STATUS CLASS
     * ============================================================
     */
    private function getStatusClass($status)
    {
        $classes = [
            'DANGER' => 'danger',
            'WARNING' => 'warning',
            'NORMAL' => 'normal',
            'OFFLINE' => 'offline',
        ];
        return $classes[$status] ?? 'normal';
    }
}