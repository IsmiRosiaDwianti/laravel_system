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
     *  🔥 KONFIGURASI THRESHOLD PPM (Ubah angka di sini!)
     * ============================================================
     *  NORMAL   : 0 - (WARNING_THRESHOLD - 1)
     *  WARNING  : WARNING_THRESHOLD - (DANGER_THRESHOLD - 1)
     *  DANGER   : >= DANGER_THRESHOLD
     * ============================================================
     */
    private const WARNING_THRESHOLD = 200;  // Mulai Warning dari 200 ppm
    private const DANGER_THRESHOLD  = 500;  // Mulai Danger dari 500 ppm

    /**
     * ============================================================
     *  🔧 NAMA DEVICE DEFAULT (Hanya 1 device)
     * ============================================================
     */
    private const DEFAULT_DEVICE_NAME = 'ESP32-Smoke';
    private const DEFAULT_DEVICE_LOCATION = 'Ruang Server';

    /**
     * Display a listing of the smoke monitoring (WEB).
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
        
        // 🔥 Reset counter
        $normal = 0;
        $warning = 0;
        $danger = 0;

        // 🔥 Loop setiap device untuk tentukan status berdasarkan PPM
        foreach ($devices as $device) {
            $ppm = $device->smoke_value ?? 0;
            
            // 🔥 LOGIKA PENENTUAN STATUS (Gunakan konstanta di atas)
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

        // ==================== CHART LOGS (20 data terakhir) ====================
        $chartLogs = SmokeLog::latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        // ==================== LOGS UNTUK TABEL (dengan pagination) ====================
        $perPage = $request->input('perPage', 10);
        $smokeLogs = SmokeLog::with('device')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        // ==================== ONLINE COUNT UNTUK ESP STATUS ====================
        $onlineCount = $online;

        // ==================== KIRIM KE VIEW ====================
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
            'smokeLogs'
        ));
    }

    /**
     * ============================================================
     *  📡 ESP32 KIRIM DATA (POST /api/smoke)
     *  ============================================================
     *  🔗 URL: POST /api/smoke
     *  📦 Body: { "ppm": 350 }
     *  🔑 Tidak perlu Auth (biar ESP32 mudah kirim data)
     * ============================================================
     */
    public function receiveData(Request $request)
    {
        try {
            // 🔥 VALIDASI INPUT
            $validated = $request->validate([
                'ppm' => 'required|integer|min:0|max:10000',
            ]);

            $ppm = $validated['ppm'];

            // 🔥 CARI ATAU BUAT DEVICE (HANYA 1)
            $device = SmokeDevice::first();
            if (!$device) {
                $device = SmokeDevice::create([
                    'name' => self::DEFAULT_DEVICE_NAME,
                    'location' => self::DEFAULT_DEVICE_LOCATION,
                    'device_status' => 'ONLINE',
                    'smoke_value' => $ppm,
                    'status' => 'NORMAL',
                    'last_seen_at' => Carbon::now(),
                ]);
            }

            // 🔥 SIMPAN STATUS LAMA UNTUK CEK PERUBAHAN
            $oldStatus = $device->status;

            // 🔥 TENTUKAN STATUS BERDASARKAN PPM
            if ($ppm >= self::DANGER_THRESHOLD) {
                $status = 'DANGER';
                $message = "🔥 Asap tinggi! {$ppm} ppm - Segera periksa!";
                $icon = '🔥';
            } elseif ($ppm >= self::WARNING_THRESHOLD) {
                $status = 'WARNING';
                $message = "⚠️ Asap terdeteksi! {$ppm} ppm - Waspada!";
                $icon = '⚠️';
            } else {
                $status = 'NORMAL';
                $message = "✅ Kondisi aman ({$ppm} ppm)";
                $icon = '✅';
            }

            // 🔥 UPDATE DEVICE
            $device->update([
                'smoke_value' => $ppm,
                'status' => $status,
                'device_status' => 'ONLINE',
                'last_seen_at' => Carbon::now(),
            ]);

            // 🔥 SIMPAN LOG
            $log = SmokeLog::create([
                'smoke_device_id' => $device->id,
                'smoke_value' => $ppm,
                'status' => $status,
                'message' => $message,
            ]);

            // 🔥 KIRIM WA JIKA STATUS BERUBAH (NORMAL → WARNING/DANGER)
            if ($oldStatus != $status) {
                $this->sendSmokeAlert($device, $ppm, $status, $oldStatus);
            }

            // 🔥 LOG KE FILE (untuk debugging)
            Log::info("📡 Data dari ESP32: {$ppm} ppm - {$status}");

            // 🔥 RESPONSE SUKSES
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dikirim',
                'data' => [
                    'ppm' => $ppm,
                    'status' => $status,
                    'status_label' => $this->getStatusLabel($status),
                    'status_class' => $this->getStatusClass($status),
                    'icon' => $icon,
                    'log_id' => $log->id,
                    'device_name' => $device->name,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
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
     *  🔥 KIRIM WHATSAPP ALERT UNTUK ASAP (WARNING/DANGER)
     *  ============================================================
     */
    private function sendSmokeAlert($device, $ppm, $status, $oldStatus)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA smoke alert');
            return;
        }

        // 🔥 BUAT PESAN BERDASARKAN STATUS
        if ($status == 'DANGER') {
            $message = 
"🔴 DANGER! ASAP TINGGI!

📡 Device : {$device->name}
📍 Lokasi : {$device->location}
📊 Nilai Asap : {$ppm} ppm

⚠️ Status : DANGER (Berbahaya!)
🔄 Sebelumnya : {$oldStatus}

🔍 TINDAKAN YANG HARUS DILAKUKAN:
================================
1️⃣ 🏃 SEGERA EVAKUASI!
2️⃣ 🔥 Matikan sumber api / listrik
3️⃣ 🚒 Hubungi petugas pemadam
4️⃣ 🚪 Buka ventilasi / pintu

📱 Jangan panik! Tetap tenang dan evakuasi dengan aman!

🕐 " . now()->format('d-m-Y H:i:s');

        } elseif ($status == 'WARNING') {
            $message = 
"🟡 PERINGATAN ASAP!

📡 Device : {$device->name}
📍 Lokasi : {$device->location}
📊 Nilai Asap : {$ppm} ppm

⚠️ Status : WARNING (Waspada!)
🔄 Sebelumnya : {$oldStatus}

🔍 TINDAKAN YANG HARUS DILAKUKAN:
================================
1️⃣ 🔍 Periksa sumber asap
2️⃣ 💨 Buka ventilasi / jendela
3️⃣ 🧯 Siapkan APAR jika diperlukan
4️⃣ 📱 Pantau terus kondisi asap

🕐 " . now()->format('d-m-Y H:i:s');

        } else {
            // NORMAL (tidak kirim WA)
            return;
        }

        // 🔥 KIRIM KE SEMUA KONTAK AKTIF
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
     *  ============================================================
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
     *  📜 GET LOGS HISTORY (GET /api/smoke/logs)
     *  ============================================================
     */
    public function getLogs(Request $request)
    {
        try {
            $limit = $request->input('limit', 50);
            
            $logs = SmokeLog::with('device')
                ->latest()
                ->take($limit)
                ->get()
                ->map(function($log) {
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
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $logs,
                'total' => $logs->count(),
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
     *  📊 API: Ambil data smoke terbaru (untuk chart real-time)
     *  ============================================================
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
     *  ============================================================
     */
    public function getDeviceStatus()
    {
        try {
            $devices = SmokeDevice::all()->map(function($device) {
                $ppm = $device->smoke_value ?? 0;
                
                // 🔥 Tentukan status
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
     *  🔥 UPDATE THRESHOLD (Opsional: via API)
     *  ============================================================
     */
    public function updateThreshold(Request $request)
    {
        try {
            $request->validate([
                'warning' => 'required|integer|min:0',
                'danger' => 'required|integer|min:0|gt:warning',
            ]);

            // Simpan ke database atau config
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
     *  🔗 URL: GET /smoke-detector/export
     *  🔑 Butuh Auth (user login)
     *  📦 Filter (opsional): ?date_from=2026-01-01&date_to=2026-01-31&status=WARNING
     * ============================================================
     */
    public function export(Request $request)
    {
        try {
            // 🔥 AMBIL SEMUA LOG DENGAN FILTER (OPSIONAL)
            $query = SmokeLog::with('device')->orderBy('created_at', 'desc');

            // Filter berdasarkan tanggal (opsional)
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Filter berdasarkan status (opsional)
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $logs = $query->get();

            // Jika tidak ada data
            if ($logs->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('warning', 'Tidak ada data untuk diexport');
            }

            // Nama file
            $filename = 'smoke_logs_' . date('Y-m-d_H-i-s') . '.csv';

            // Header CSV
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            // 🔥 BUAT CSV
            $callback = function() use ($logs) {
                $file = fopen('php://output', 'w');
                
                // BOM untuk UTF-8 (biar Excel baca dengan benar)
                fputs($file, "\xEF\xBB\xBF");

                // 🔥 HEADER CSV
                fputcsv($file, [
                    'No',
                    'Tanggal & Waktu',
                    'Device',
                    'Nilai Asap (ppm)',
                    'Status',
                    'Keterangan'
                ]);

                // 🔥 DATA
                $no = 1;
                foreach ($logs as $log) {
                    // Tentukan status label
                    $statusLabel = $log->status ?? 'NORMAL';
                    $statusIcon = match($statusLabel) {
                        'DANGER' => '🔴 DANGER',
                        'WARNING' => '🟡 WARNING',
                        default => '🟢 NORMAL',
                    };

                    // Tentukan pesan
                    $message = $log->message ?? match($statusLabel) {
                        'DANGER' => '🔥 Asap tinggi! Periksa segera!',
                        'WARNING' => '⚠️ Asap mulai terdeteksi, waspada!',
                        default => '✅ Kondisi aman, tidak ada asap',
                    };

                    fputcsv($file, [
                        $no++,
                        $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                        $log->device->name ?? 'Unknown Device',
                        number_format($log->smoke_value ?? 0, 0),
                        $statusIcon,
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
        ];
        return $classes[$status] ?? 'normal';
    }
}