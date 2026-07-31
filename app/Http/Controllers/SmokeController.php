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
     *  🔥 KONFIGURASI THRESHOLD (LANGSUNG ADC DARI ARDUINO)
     *  SAMA PERSIS DENGAN ARDUINO
     * ============================================================
     */
    private const WARNING_THRESHOLD = 700;   // SAMA DENGAN ARDUINO
    private const DANGER_THRESHOLD  = 1000;  // SAMA DENGAN ARDUINO
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
            $adc = $device->smoke_value ?? 0;
            
            if ($adc >= self::DANGER_THRESHOLD) {
                $danger++;
                $device->status = 'DANGER';
                $device->status_label = '🔴 DANGER';
                $device->status_class = 'danger';
                $device->status_icon = '🔥';
            } elseif ($adc >= self::WARNING_THRESHOLD) {
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

        // ==================== 🔥 LOGS UNTUK TABEL (DENGAN SORTIR) ====================
        $perPage = $request->input('perPage', 10);
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // 🔥 VALIDASI SORT UNTUK MENCEGAH SQL INJECTION
        $allowedSorts = ['id', 'created_at', 'smoke_value', 'status', 'message', 'updated_at'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        // 🔥 AMBIL SEMUA LOG DARI DATABASE
        $allLogs = SmokeLog::with('device')
            ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
            ->orderBy($sort, $direction)
            ->get();

        // 🔥 FILTER: HANYA TAMPILKAN PERUBAHAN STATUS
        $filteredLogs = [];
        $lastStatus = null;
        
        foreach ($allLogs as $log) {
            if ($lastStatus === null || $log->status !== $lastStatus) {
                $filteredLogs[] = $log;
                $lastStatus = $log->status;
            }
        }
        
        $totalFiltered = count($filteredLogs);
        
        // 🔥 PAGINATION MANUAL (KARENA FILTER)
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = array_slice($filteredLogs, $offset, $perPage);
        
        $smokeLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            $totalFiltered,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
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
     *  MENERIMA ADC DARI ARDUINO
     * ============================================================
     */
    public function receiveData(Request $request)
    {
        try {
            $validated = $request->validate([
                'adc' => 'required|integer|min:0|max:4095',
            ]);

            $adc = $validated['adc'];

            $device = SmokeDevice::first();
            if (!$device) {
                $device = SmokeDevice::create([
                    'name' => self::DEFAULT_DEVICE_NAME,
                    'location' => self::DEFAULT_DEVICE_LOCATION,
                    'device_status' => 'ONLINE',
                    'smoke_value' => $adc,
                    'status' => 'NORMAL',
                    'last_seen_at' => Carbon::now(),
                    'last_wa_sent_at' => null,
                ]);
            }

            $wasOffline = ($device->device_status === 'OFFLINE');
            $oldStatus = $device->status;
            $oldAdc = $device->smoke_value ?? 0;

            if ($adc >= self::DANGER_THRESHOLD) {
                $status = 'DANGER';
                $message = "🔥 Asap tinggi!: {$adc}";
                $icon = '🔥';
            } elseif ($adc >= self::WARNING_THRESHOLD) {
                $status = 'WARNING';
                $message = "⚠️ Asap terdeteksi! Nilai Asap: {$adc}";
                $icon = '⚠️';
            } else {
                $status = 'NORMAL';
                $message = "✅ Kondisi aman | Nilai Asap: {$adc}";
                $icon = '✅';
            }

            $isStatusChanged = ($oldStatus != $status);
            $log = null;
            $isNewLogSaved = false;
            $isAdcUpdated = false;
            $lastLog = null;
            $updatedLog = null;

            if ($isStatusChanged) {
                $log = SmokeLog::create([
                    'smoke_device_id' => $device->id,
                    'smoke_value' => $adc,
                    'status' => $status,
                    'message' => $message,
                ]);
                $isNewLogSaved = true;
                $updatedLog = $log;
                Log::info("📝 Log baru: Status berubah dari {$oldStatus} ke {$status} (ADC: {$adc})");
            } else {
                $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                    ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastLog) {
                    $lastLog->smoke_value = $adc;
                    $lastLog->updated_at = Carbon::now();
                    $lastLog->save();
                    $isAdcUpdated = true;
                    $updatedLog = $lastLog;
                    Log::info("🔄 Log diupdate: Status {$status} tetap, ADC: {$oldAdc} → {$adc}");
                } else {
                    $log = SmokeLog::create([
                        'smoke_device_id' => $device->id,
                        'smoke_value' => $adc,
                        'status' => $status,
                        'message' => $message,
                    ]);
                    $isNewLogSaved = true;
                    $updatedLog = $log;
                    Log::info("📝 Log pertama: {$status} (ADC: {$adc})");
                }
            }

            $device->update([
                'smoke_value' => $adc,
                'status' => $status,
                'device_status' => 'ONLINE',
                'last_seen_at' => Carbon::now(),
            ]);

            // 🔥 KIRIM WA JIKA KEMBALI ONLINE (1x)
            if ($wasOffline) {
                Log::info("📱 ESP kembali online, kirim WA online alert");
                $this->sendEspOnlineAlert($device);
            }

            // 🔥🔥🔥 KIRIM WA SMOKE HANYA KETIKA STATUS NAIK
            $isStatusUp = false;
            
            if ($isStatusChanged) {
                // HANYA KIRIM JIKA STATUS NAIK (NORMAL→WARNING, NORMAL→DANGER, WARNING→DANGER)
                if ($oldStatus == 'NORMAL' && in_array($status, ['WARNING', 'DANGER'])) {
                    $isStatusUp = true;
                } elseif ($oldStatus == 'WARNING' && $status == 'DANGER') {
                    $isStatusUp = true;
                }
                
                if ($isStatusUp) {
                    $this->sendSmokeAlert($device, $adc, $status);
                    $device->update(['last_wa_sent_at' => Carbon::now()]);
                    Log::info("📱 WA SMOKE dikirim: {$oldStatus} → {$status} (ADC: {$adc})");
                } else {
                    Log::info("⏭️ WA SMOKE skip (status turun): {$oldStatus} → {$status} (ADC: {$adc})");
                }
            } else {
                Log::info("⏭️ WA SMOKE skip: Status {$status} tetap (ADC: {$oldAdc} → {$adc})");
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diproses',
                'data' => [
                    'adc' => $adc,
                    'old_adc' => $oldAdc,
                    'status' => $status,
                    'old_status' => $oldStatus,
                    'status_label' => $this->getStatusLabel($status),
                    'status_class' => $this->getStatusClass($status),
                    'icon' => $icon,
                    'log_id' => $log?->id ?? $lastLog?->id,
                    'is_status_changed' => $isStatusChanged,
                    'is_new_log_saved' => $isNewLogSaved,
                    'is_adc_updated' => $isAdcUpdated,
                    'device_name' => $device->name,
                    'was_offline' => $wasOffline,
                    'created_at' => $log?->created_at?->format('Y-m-d H:i:s') ?? $lastLog?->created_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'updated_at' => $lastLog?->updated_at?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
                    'wa_sent' => $isStatusUp,
                    'wa_reason' => $isStatusUp ? "Status naik {$oldStatus}→{$status}" : "Status turun/tetap",
                    'latest_log' => $updatedLog ? [
                        'id' => $updatedLog->id,
                        'adc' => $updatedLog->smoke_value,
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
     *  DIPANGGIL OTOMATIS OLEH SCHEDULER
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
                $isOnline = $device->last_seen_at &&
                            Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;
                
                $oldDeviceStatus = $device->device_status;
                $newDeviceStatus = $isOnline ? 'ONLINE' : 'OFFLINE';
                
                $device->device_status = $newDeviceStatus;
                $device->save();
                
                $statusChanged = ($oldDeviceStatus !== $newDeviceStatus);
                
                if (!$isOnline) {
                    $lastSeen = Carbon::parse($device->last_seen_at);
                    $minutesDiff = $lastSeen->diffInMinutes(now());
                    
                    Log::info("🚨 Device {$device->name} OFFLINE! - {$minutesDiff} menit");
                    
                    // 🔥 KIRIM WA HANYA JIKA STATUS BERUBAH (ONLINE → OFFLINE) DAN > 2 MENIT
                    if ($minutesDiff >= 2 && $statusChanged) {
                        Log::info("📤📤📤 MENGIRIM WA ESP OFFLINE...");
                        $this->sendEspOfflineAlert($device, $minutesDiff);
                    } else {
                        Log::info("⏭️ Skip WA offline: minutesDiff={$minutesDiff}, statusChanged=" . ($statusChanged ? 'YES' : 'NO'));
                    }
                } else {
                    Log::info("✅ Device {$device->name} ONLINE");
                    
                    // 🔥 KIRIM WA HANYA JIKA STATUS BERUBAH (OFFLINE → ONLINE)
                    if ($statusChanged) {
                        Log::info("🟢🟢🟢 MENGIRIM WA ESP ONLINE...");
                        $this->sendEspOnlineAlert($device);
                    } else {
                        Log::info("⏭️ Skip WA online: status sudah ONLINE");
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
                    'status_changed' => $statusChanged,
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
     *  ⚠️ KIRIM WA ESP OFFLINE (HANYA 1x)
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
📊 Status terakhir: {$device->smoke_value} ADC

🔍 TINDAKAN YANG HARUS DILAKUKAN:
1️⃣ Cek power / sumber listrik ESP
2️⃣ Cek koneksi Internet
3️⃣ Kemungkinan Sedang dalam pergantian password

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
     *  🟢 KIRIM WA ESP ONLINE (HANYA 1x)
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
📊 Smoke Value: {$device->smoke_value} ADC
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
     *  🔥 KIRIM WHATSAPP SMOKE ALERT (HANYA KETIKA STATUS NAIK)
     * ============================================================
     */
    private function sendSmokeAlert($device, $adc, $status)
    {
        // HANYA KIRIM UNTUK WARNING ATAU DANGER
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

📊 Nilai ADC : {$adc}
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

📊 Nilai ADC : {$adc}
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

    /**
     * ============================================================
     *  📤 EXPORT CSV
     * ============================================================
     */
    public function export(Request $request)
    {
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        
        $allowedSorts = ['id', 'created_at', 'smoke_value', 'status', 'message'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
        
        $logs = SmokeLog::with('device')
            ->orderBy($sort, $direction)
            ->get();
        
        $filename = 'smoke_logs_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, ['ID', 'Tanggal', 'Nilai Asap', 'Status', 'Keterangan']);
            
            // Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                    $log->smoke_value ?? 0,
                    $log->status ?? 'NORMAL',
                    $log->message ?? '-',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}