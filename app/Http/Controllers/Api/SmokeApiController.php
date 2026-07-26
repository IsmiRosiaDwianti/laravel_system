<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmokeDevice;
use App\Models\SmokeLog;
use App\Models\Contact;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmokeApiController extends Controller
{
    /**
     * ============================================================
     *  🔥 KONFIGURASI THRESHOLD
     * ============================================================
     */
    private const WARNING_THRESHOLD = 700;
    private const DANGER_THRESHOLD  = 1000;
    private const DEFAULT_DEVICE_NAME = 'ESP32-Smoke';
    private const DEFAULT_DEVICE_LOCATION = 'Ruang Server';

    /**
     * ============================================================
     *  📊 GET STATUS TERBARU
     *  ============================================================
     *  🔗 URL: GET /api/smoke/status
     *  🔑 Butuh Auth: Optional (bisa tanpa token untuk ESP)
     *  📤 Response: Status smoke terbaru
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
                        'adc' => 0,
                        'status' => 'NORMAL',
                        'status_label' => '🟢 NORMAL',
                        'status_class' => 'normal',
                        'device_status' => 'OFFLINE',
                        'last_seen_at' => null,
                        'is_status_changed' => false,
                        'is_adc_updated' => false,
                        'latest_log' => null,
                    ]
                ]);
            }

            $adc = $device->smoke_value ?? 0;
            
            if ($adc >= self::DANGER_THRESHOLD) {
                $status = 'DANGER';
                $label = '🔴 DANGER';
                $class = 'danger';
            } elseif ($adc >= self::WARNING_THRESHOLD) {
                $status = 'WARNING';
                $label = '🟡 WARNING';
                $class = 'warning';
            } else {
                $status = 'NORMAL';
                $label = '🟢 NORMAL';
                $class = 'normal';
            }

            $isOnline = $device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;

            $lastLog = SmokeLog::where('smoke_device_id', $device->id)
                ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            $isStatusChanged = false;
            $isAdcUpdated = false;
            
            if ($lastLog) {
                if ($lastLog->status != $status) {
                    $isStatusChanged = true;
                }
                elseif ($lastLog->status == $status && $lastLog->smoke_value != $adc) {
                    $isAdcUpdated = true;
                }
            }

            $latestLogData = null;
            if ($lastLog) {
                $latestLogData = [
                    'id' => $lastLog->id,
                    'adc' => $lastLog->smoke_value,
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
                    'adc' => $adc,
                    'status' => $status,
                    'status_label' => $label,
                    'status_class' => $class,
                    'device_status' => $isOnline ? 'ONLINE' : 'OFFLINE',
                    'last_seen_at' => $device->last_seen_at?->format('Y-m-d H:i:s'),
                    'last_seen_human' => $device->last_seen_at?->diffForHumans(),
                    'is_status_changed' => $isStatusChanged,
                    'is_adc_updated' => $isAdcUpdated,
                    'last_log_status' => $lastLog?->status,
                    'last_log_adc' => $lastLog?->smoke_value,
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
     *  📜 GET LOGS HISTORY (HANYA PERUBAHAN STATUS)
     *  ============================================================
     *  🔗 URL: GET /api/smoke/logs
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Query: ?limit=50
     *  📤 Response: Daftar log perubahan status
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
                    'adc' => $log->smoke_value,
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
     *  📊 GET LATEST DATA UNTUK CHART
     *  ============================================================
     *  🔗 URL: GET /api/smoke/latest
     *  🔑 Butuh Auth: Sanctum Token
     *  📤 Response: 20 data terakhir untuk chart
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
     *  📡 GET STATUS SEMUA DEVICE
     *  ============================================================
     *  🔗 URL: GET /api/smoke/devices
     *  🔑 Butuh Auth: Sanctum Token
     *  📤 Response: Status semua device ESP
     * ============================================================
     */
    public function getDeviceStatus()
    {
        try {
            $devices = SmokeDevice::all()->map(function($device) {
                $adc = $device->smoke_value ?? 0;
                
                if ($adc >= self::DANGER_THRESHOLD) {
                    $status = 'DANGER';
                    $label = '🔴 DANGER';
                    $class = 'danger';
                } elseif ($adc >= self::WARNING_THRESHOLD) {
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
                    'smoke_value' => $adc,
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
     *  📡 ESP32 KIRIM DATA (POST /api/smoke/receive)
     *  ============================================================
     *  🔗 URL: POST /api/smoke/receive
     *  🔑 Butuh Auth: Tidak (untuk ESP32)
     *  📦 Body: { "adc": 850 }
     *  📤 Response: Status pemrosesan data
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
                } else {
                    $log = SmokeLog::create([
                        'smoke_device_id' => $device->id,
                        'smoke_value' => $adc,
                        'status' => $status,
                        'message' => $message,
                    ]);
                    $isNewLogSaved = true;
                    $updatedLog = $log;
                }
            }

            $device->update([
                'smoke_value' => $adc,
                'status' => $status,
                'device_status' => 'ONLINE',
                'last_seen_at' => Carbon::now(),
            ]);

            if ($wasOffline) {
                $this->sendEspOnlineAlert($device);
            }

            $isStatusUp = false;
            
            if ($isStatusChanged) {
                if ($oldStatus == 'NORMAL' && in_array($status, ['WARNING', 'DANGER'])) {
                    $isStatusUp = true;
                } elseif ($oldStatus == 'WARNING' && $status == 'DANGER') {
                    $isStatusUp = true;
                }
                
                if ($isStatusUp) {
                    $this->sendSmokeAlert($device, $adc, $status);
                    $device->update(['last_wa_sent_at' => Carbon::now()]);
                }
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
     *  🔍 CHECK ESP STATUS (UNTUK SCHEDULER)
     *  ============================================================
     *  🔗 URL: GET /api/smoke/check-status
     *  🔑 Butuh Auth: Sanctum Token
     *  📤 Response: Status semua ESP (online/offline)
     * ============================================================
     */
    public function checkEspStatus()
    {
        try {
            $devices = SmokeDevice::all();

            if ($devices->isEmpty()) {
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
                    
                    if ($minutesDiff >= 2 && $statusChanged) {
                        $this->sendEspOfflineAlert($device, $minutesDiff);
                    }
                } else {
                    if ($statusChanged) {
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
                    'status_changed' => $statusChanged,
                ];
            }
            
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
     *  🔥 UPDATE THRESHOLD
     *  ============================================================
     *  🔗 URL: POST /api/smoke/threshold
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Body: { "warning": 700, "danger": 1000 }
     *  📤 Response: Threshold baru
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
     *  ============================================================
     *  🔗 URL: GET /api/smoke/export
     *  🔑 Butuh Auth: Sanctum Token
     *  📤 Response: File CSV
     * ============================================================
     */
    public function export()
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
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data untuk diexport',
                ], 404);
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
                    'Nilai ADC',
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
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ================================================================
    // 🔧 PRIVATE HELPER METHODS
    // ================================================================

    /**
     * ⚠️ KIRIM WA ESP OFFLINE
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
     * 🟢 KIRIM WA ESP ONLINE
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
     * 🔥 KIRIM WHATSAPP SMOKE ALERT
     */
    private function sendSmokeAlert($device, $adc, $status)
    {
        if (!in_array($status, ['WARNING', 'DANGER'])) {
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
     * 📊 Helper: GET STATUS LABEL
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
     * 📊 Helper: GET STATUS CLASS
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