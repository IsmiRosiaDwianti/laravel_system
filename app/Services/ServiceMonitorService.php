<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;

class ServiceMonitorService
{
    // 🔥 FLAG UNTUK CEK JARINGAN (HINDARI SPAM WA)
    private $networkAlertSent = false;

    public function check(Service $service)
    {
        if ($service->type === 'ping') {
            return $this->checkPing($service);
        }

        return $this->checkHttp($service);
    }

    /**
     * 🔥 CEK KONEKSI JARINGAN (CEPAT & AKURAT)
     * - Cek DNS terlebih dahulu (0.1-0.5 detik)
     * - Fallback ke HTTP request jika DNS gagal (1-2 detik)
     * 
     * 🔥 PUBLIC AGAR BISA DIPANGGIL DARI NETWORK CONTROLLER
     */
    public function checkNetworkConnection()
    {
        // 🔥 CEK DNS DULU (TERCEPAT)
        if (checkdnsrr('google.com', 'A')) {
            Log::info('Network check: Connected via DNS');
            return true;
        }
        
        // 🔥 FALLBACK 1: HTTP REQUEST (LEBIH AKURAT)
        try {
            $response = Http::timeout(3)->get('https://www.google.com');
            if ($response->successful()) {
                Log::info('Network check: Connected via HTTP');
                return true;
            }
        } catch (\Exception $e) {
            Log::info('Network check: HTTP failed - ' . $e->getMessage());
        }
        
        // 🔥 FALLBACK 2: CEK 8.8.8.8
        try {
            $response = Http::timeout(3)->get('http://8.8.8.8');
            if ($response->successful()) {
                Log::info('Network check: Connected via 8.8.8.8');
                return true;
            }
        } catch (\Exception $e) {
            Log::info('Network check: 8.8.8.8 failed');
        }
        
        Log::info('Network check: DISCONNECTED');
        return false;
    }

    /**
     * 🔥 HANYA LOG KE FILE, TIDAK KIRIM WA (hanya alert di web)
     */
    private function sendNetworkAlert()
    {
        // 🔥 HANYA LOG, TIDAK KIRIM WA
        Log::info('📡 Network alert: Jaringan terputus - ' . now()->format('d-m-Y H:i:s'));
        
        // ❌ TIDAK ADA KIRIM WA
    }

    /**
     * 🔥 HANYA LOG KE FILE, TIDAK KIRIM WA (hanya alert di web)
     */
    private function sendNetworkRestoredAlert()
    {
        // 🔥 HANYA LOG, TIDAK KIRIM WA
        Log::info('📡 Network alert: Jaringan kembali normal - ' . now()->format('d-m-Y H:i:s'));
        
        // ❌ TIDAK ADA KIRIM WA
    }

    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status;

        // 🔥 CEK JARINGAN DULU (SUDAH CEPAT)
        $isNetworkConnected = $this->checkNetworkConnection();
        
        // Kirim alert jika jaringan mati (hanya 1x) - TAPI TIDAK KIRIM WA
        if (!$isNetworkConnected && !$this->networkAlertSent) {
            $this->sendNetworkAlert();
            $this->networkAlertSent = true;
        }
        
        // Kirim alert jika jaringan kembali normal - TAPI TIDAK KIRIM WA
        if ($isNetworkConnected && $this->networkAlertSent) {
            $this->sendNetworkRestoredAlert();
            $this->networkAlertSent = false;
        }

        // 🔥🔥🔥 PERUBAHAN: JIKA JARINGAN MATI → SKIP CHECK, PERTAHANKAN STATUS 🔥🔥🔥
        if (!$isNetworkConnected) {
            Log::info("⏭️ Skip check {$service->name} karena jaringan terputus, status tetap {$oldStatus}");
            
            // ✅ UPDATE last_check_at SAJA, TIDAK UBAH STATUS
            $service->update([
                'last_check_at' => now(),
            ]);
            
            // ❌ TIDAK BUAT LOG, TIDAK KIRIM WA
            return;
        }

        // 🔥 JARINGAN NORMAL → LANJUTKAN CHECK
        try {
            $url = $service->target;

            if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                $url = 'https://' . $url;
            }

            $start = microtime(true);
            $response = Http::timeout(10)->get($url);
            $time = round(microtime(true) - $start, 2);
            $code = $response->status();

            // 🔥 ANALISIS KONTEN HALAMAN
            if ($code == 200) {
                $body = $response->body();
                
                // 🔥 CEK APAKAH HALAMAN BENAR-BENAR KOSONG
                if ($this->isEmptyResponse($body)) {
                    $analysis = [
                        'status' => 'WARNING',
                        'reason' => 'EMPTY_RESPONSE',
                        'detail' => 'Halaman kosong (tidak ada konten)',
                        'action' => 'Periksa aplikasi, kemungkinan error rendering'
                    ];
                    $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                                     $analysis['reason'], $analysis['detail'], $analysis['action']);
                    return;
                }

                // 🔥 CEK APAKAH HANYA TAG HTML KOSONG
                $contentAnalysis = $this->analyzePageContent($body);
                
                if (!$contentAnalysis['has_content']) {
                    $analysis = [
                        'status' => 'WARNING',
                        'reason' => 'NO_MEANINGFUL_CONTENT',
                        'detail' => 'Halaman hanya HTML kosong, tidak ada konten',
                        'action' => 'Periksa aplikasi, kemungkinan maintenance/error'
                    ];
                    $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                                     $analysis['reason'], $analysis['detail'], $analysis['action']);
                    return;
                }

                // 🔥 HALAMAN NORMAL
                $detail = 'Service berjalan normal';
                
                $info = [];
                if ($contentAnalysis['has_text']) {
                    $info[] = number_format($contentAnalysis['text_length']) . ' karakter';
                }
                if ($contentAnalysis['has_images']) {
                    $info[] = $contentAnalysis['image_count'] . ' gambar';
                }
                if ($contentAnalysis['has_videos']) {
                    $info[] = $contentAnalysis['video_count'] . ' video';
                }
                if ($contentAnalysis['has_links']) {
                    $info[] = $contentAnalysis['link_count'] . ' link';
                }
                
                if (!empty($info)) {
                    $detail .= ' (' . implode(', ', $info) . ')';
                }
                
                $analysis = [
                    'status' => 'UP',
                    'reason' => 'OK',
                    'detail' => $detail,
                    'action' => '-'
                ];

                $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                                 $analysis['reason'], $analysis['detail'], $analysis['action']);
                return;
            }

            $analysis = $this->analyzeResponse($code, $time);

        } catch (\Exception $e) {
            $time = 0;
            $code = 'N/A';
            $analysis = $this->analyzeException($e->getMessage());
        }

        $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                         $analysis['reason'], $analysis['detail'], $analysis['action']);
    }

    private function analyzePageContent($body)
    {
        $result = [
            'has_images' => false,
            'image_count' => 0,
            'has_videos' => false,
            'video_count' => 0,
            'has_text' => false,
            'text_length' => 0,
            'has_links' => false,
            'link_count' => 0,
            'has_content' => false,
            'details' => []
        ];

        preg_match_all('/<img[^>]+>/i', $body, $imgMatches);
        $result['image_count'] = count($imgMatches[0]);
        $result['has_images'] = $result['image_count'] > 0;

        $videoPatterns = [
            '/<video[^>]+>/i',
            '/<iframe[^>]*youtube[^>]*>/i',
            '/<iframe[^>]*vimeo[^>]*>/i',
            '/<iframe[^>]*dailymotion[^>]*>/i'
        ];
        $result['video_count'] = 0;
        foreach ($videoPatterns as $pattern) {
            preg_match_all($pattern, $body, $vidMatches);
            $result['video_count'] += count($vidMatches[0]);
        }
        $result['has_videos'] = $result['video_count'] > 0;

        $text = strip_tags($body);
        $text = preg_replace('/\s+/', '', $text);
        $result['text_length'] = strlen($text);
        $result['has_text'] = $result['text_length'] > 0;

        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $body, $linkMatches);
        $result['link_count'] = count($linkMatches[0]);
        $result['has_links'] = $result['link_count'] > 0;

        $result['has_content'] = $result['has_images'] || 
                                 $result['has_videos'] || 
                                 $result['has_text'] || 
                                 $result['has_links'];

        if ($result['has_images']) {
            $result['details'][] = "🖼️ {$result['image_count']} gambar";
        }
        if ($result['has_videos']) {
            $result['details'][] = "🎬 {$result['video_count']} video";
        }
        if ($result['has_text']) {
            $result['details'][] = "📝 " . number_format($result['text_length']) . " karakter";
        }
        if ($result['has_links']) {
            $result['details'][] = "🔗 {$result['link_count']} link";
        }

        return $result;
    }

    private function isEmptyResponse($body)
    {
        $cleaned = preg_replace('/\s+/', '', strip_tags($body));
        return strlen($cleaned) === 0;
    }

    /**
     * 🔥 CHECK PING SERVICE - DIPERBAIKI
     */
    private function checkPing(Service $service)
    {
        $oldStatus = $service->last_status;

        // 🔥 CEK JARINGAN DULU
        $isNetworkConnected = $this->checkNetworkConnection();
        
        if (!$isNetworkConnected && !$this->networkAlertSent) {
            $this->sendNetworkAlert();
            $this->networkAlertSent = true;
        }
        
        if ($isNetworkConnected && $this->networkAlertSent) {
            $this->sendNetworkRestoredAlert();
            $this->networkAlertSent = false;
        }

        // 🔥🔥🔥 PERUBAHAN: JIKA JARINGAN MATI → SKIP CHECK, PERTAHANKAN STATUS 🔥🔥🔥
        if (!$isNetworkConnected) {
            Log::info("⏭️ Skip ping check {$service->name} karena jaringan terputus, status tetap {$oldStatus}");
            $service->update([
                'last_check_at' => now(),
            ]);
            return; // ✅ LANGSUNG KELUAR, TIDAK BUAT LOG
        }

        // 🔥 INTERNET NORMAL → LANJUTKAN CHECK PING
        $start = microtime(true);
        exec("ping -n 1 " . escapeshellarg($service->target), $output, $result);
        $time = round(microtime(true) - $start, 2);

        if ($result === 0) {
            $status = 'UP';
            $code = 'PING';
            $reason = 'PING_OK';
            $detail = 'Host merespon ping';
            $action = '-';
        } else {
            $status = 'DOWN';
            $code = 'N/A';
            $reason = 'PING_FAILED';
            $detail = 'Host tidak merespon ping';
            $action = 'Cek koneksi jaringan atau pastikan device menyala';
        }

        $this->saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action);
    }

    /**
     * 🔥 SAVE RESULT DENGAN LOGIKA PENGIRIMAN WA YANG DIPERBAIKI
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail,
            'last_check_at' => now(),
        ]);

        if ($oldStatus != $status) {
            // 🔥 BUAT LOG
            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $status,
                'response_code' => $code,
                'response_time' => $time,
                'message' => $detail,
                'action' => $action,
                'checked_at' => now(),
            ]);

            Log::info("📝 Log dibuat untuk {$service->name}: {$oldStatus} → {$status}");

            // 🔥 CEK APAKAH INI SERVICE BARU (BELUM PERNAH ADA LOG SEBELUMNYA)
            $logCount = ServiceLog::where('service_id', $service->id)->count();
            
            // 🔥 LOGIKA PENGIRIMAN WA
            $shouldSendWA = false;
            
            if ($logCount == 1) {
                // 🔥 SERVICE BARU (hanya 1 log yaitu yang baru dibuat)
                if (in_array($status, ['DOWN', 'WARNING'])) {
                    $shouldSendWA = true;
                    Log::info("📱 Service baru {$service->name} dengan status {$status} → KIRIM WA");
                } else {
                    Log::info("📱 Service baru {$service->name} dengan status UP → TIDAK KIRIM WA");
                }
            } else {
                // 🔥 SERVICE LAMA (sudah ada log sebelumnya) - KIRIM UNTUK SEMUA PERUBAHAN
                $shouldSendWA = true;
                Log::info("📱 Perubahan status {$service->name}: {$oldStatus} → {$status} → KIRIM WA");
            }

            // 🔥 KIRIM WA JIKA MEMENUHI SYARAT
            if ($shouldSendWA) {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            }

        } else {
            // 🔥 STATUS TIDAK BERUBAH - UPDATE WAKTU SAJA
            $lastLog = ServiceLog::where('service_id', $service->id)
                ->latest()
                ->first();
            
            if ($lastLog) {
                $lastLog->update([
                    'checked_at' => now(),
                ]);
                Log::info("🔄 Update check time untuk {$service->name}: status tetap {$status}");
            }
        }
    }

    private function analyzeResponse($code, $time)
    {
        // 🔥 MAP STATUS CODE KE PESAN SIMPEL
        $statusMap = [
            200 => ['status' => 'UP', 'reason' => 'OK', 'detail' => 'Service berjalan normal', 'action' => '-'],
            301 => ['status' => 'UP', 'reason' => 'HTTP_301', 'detail' => 'Redirect permanen', 'action' => 'Update URL endpoint'],
            302 => ['status' => 'UP', 'reason' => 'HTTP_302', 'detail' => 'Redirect sementara', 'action' => 'Periksa redirect'],
            401 => ['status' => 'UP', 'reason' => 'HTTP_401', 'detail' => 'Unauthorized - Login diperlukan', 'action' => 'Cek API Key/Token'],
            403 => ['status' => 'UP', 'reason' => 'HTTP_403', 'detail' => 'Forbidden - Akses ditolak', 'action' => 'Cek izin akses / IP whitelist'],
            404 => ['status' => 'WARNING', 'reason' => 'HTTP_404', 'detail' => 'Halaman tidak ditemukan', 'action' => 'Periksa URL endpoint'],
            405 => ['status' => 'WARNING', 'reason' => 'HTTP_405', 'detail' => 'Method HTTP tidak diizinkan', 'action' => 'Ganti method HTTP (GET/POST/PUT)'],
            429 => ['status' => 'WARNING', 'reason' => 'HTTP_429', 'detail' => 'Too Many Requests - Rate limit', 'action' => 'Kurangi frekuensi request'],
            500 => ['status' => 'DOWN', 'reason' => 'HTTP_500', 'detail' => 'Internal Server Error', 'action' => 'Cek log server, periksa kode aplikasi'],
            502 => ['status' => 'DOWN', 'reason' => 'HTTP_502', 'detail' => 'Bad Gateway', 'action' => 'Periksa proxy / load balancer'],
            503 => ['status' => 'DOWN', 'reason' => 'HTTP_503', 'detail' => 'Service Unavailable', 'action' => 'Cek maintenance / scale up resource'],
            504 => ['status' => 'DOWN', 'reason' => 'HTTP_504', 'detail' => 'Gateway Timeout', 'action' => 'Optimasi response time server'],
        ];

        // 🔥 CEK APAKAH CODE ADA DI MAP
        if (isset($statusMap[$code])) {
            $result = $statusMap[$code];
            
            // Tambahkan info waktu jika slow response
            if ($code == 200 && $time > 3) {
                $result['status'] = 'WARNING';
                $result['reason'] = 'SLOW_RESPONSE';
                $result['detail'] = "Response lambat ({$time}s)";
                $result['action'] = 'Optimasi performa server (cache, database)';
            }
            
            return $result;
        }

        // 🔥 DEFAULT UNKNOWN
        return [
            'status' => 'WARNING',
            'reason' => 'HTTP_ERROR',
            'detail' => "HTTP {$code} - Kode tidak dikenal",
            'action' => 'Periksa dokumentasi API'
        ];
    }

    private function analyzeException($message)
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'timed out')) {
            return [
                'reason' => 'TIMEOUT',
                'detail' => 'Server timeout - terlalu lama merespon',
                'action' => 'Optimasi performa server, tambah timeout'
            ];
        }

        if (str_contains($msg, 'connection refused')) {
            return [
                'reason' => 'CONNECTION_REFUSED',
                'detail' => 'Koneksi ditolak - Port tidak terbuka',
                'action' => 'Server mati / firewall blocking / service belum jalan'
            ];
        }

        if (str_contains($msg, 'could not resolve')) {
            return [
                'reason' => 'DNS_ERROR',
                'detail' => 'DNS tidak ditemukan - Domain tidak terdaftar',
                'action' => 'Periksa DNS / domain / koneksi internet'
            ];
        }

        if (str_contains($msg, 'ssl') || str_contains($msg, 'certificate')) {
            return [
                'reason' => 'SSL_ERROR',
                'detail' => 'SSL/TLS Certificate Error',
                'action' => 'Periksa sertifikat SSL, perbarui jika expired'
            ];
        }

        if (str_contains($msg, 'no route to host')) {
            return [
                'reason' => 'NO_ROUTE_TO_HOST',
                'detail' => 'Tidak ada route ke host',
                'action' => 'Cek koneksi jaringan / firewall / routing'
            ];
        }

        if (str_contains($msg, 'network is unreachable')) {
            return [
                'reason' => 'NETWORK_UNREACHABLE',
                'detail' => 'Jaringan tidak dapat dijangkau',
                'action' => 'Cek koneksi internet dan jaringan lokal'
            ];
        }

        return [
            'reason' => 'UNKNOWN',
            'detail' => $message,
            'action' => 'Periksa service secara manual, cek log server'
        ];
    }

    /**
     * 🔥 KIRIM WA - HANYA UNTUK PERUBAHAN STATUS (DOWN, WARNING, UP)
     * PESAN SIMPEL & RINGKAS
     */
    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($contacts->isEmpty()) {
            Log::warning('⚠️ Tidak ada kontak aktif untuk kirim WA service alert');
            return;
        }

        // 🔥 FORMAT PESAN BERDASARKAN STATUS
        if ($status == 'DOWN') {
            $message = 
"🔴 SERVICE DOWN

📌 {$service->name}
🔗 {$service->target}

Status : DOWN
Code   : {$code}
Waktu  : {$time}s

💡 {$detail}
🔧 {$action}

🕐 " . now()->format('d-m-Y H:i:s');

        } elseif ($status == 'WARNING') {
            $message = 
"🟠 SERVICE WARNING

📌 {$service->name}
🔗 {$service->target}

Status : WARNING
Code   : {$code}
Waktu  : {$time}s

💡 {$detail}
🔧 {$action}

🕐 " . now()->format('d-m-Y H:i:s');

        } else {
            // 🔥 STATUS UP - PESAN SINGKAT
            $message = 
"🟢 SERVICE NORMAL

📌 {$service->name}
🔗 {$service->target}

Status : UP
Code   : {$code}
Waktu  : {$time}s

🕐 " . now()->format('d-m-Y H:i:s');
        }

        // 🔥 KIRIM KE SEMUA KONTAK AKTIF
        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) {
                Log::info("📱 WA service alert dikirim ke: {$contact->phone} - {$status}");
            } else {
                Log::error("❌ Gagal kirim WA service alert ke: {$contact->phone}");
            }
        }
    }
}