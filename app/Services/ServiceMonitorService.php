<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use App\Services\FonnteService;

class ServiceMonitorService
{
    public function check(Service $service)
    {
        if ($service->type === 'ping') {
            return $this->checkPing($service);
        }

        return $this->checkHttp($service);
    }

    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status;

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
                
                // 🔥 Analisis lengkap konten
                $contentAnalysis = $this->analyzePageContent($body);
                
                // 🔥 CEK APAKAH HALAMAN KOSONG (TIDAK ADA KONTEN SAMA SEKALI)
                if ($this->isEmptyResponse($body)) {
                    $analysis = [
                        'status' => 'WARNING',
                        'reason' => 'EMPTY_RESPONSE',
                        'detail' => '📄 Halaman benar-benar kosong (tidak ada konten sama sekali)',
                        'action' => '📄 Periksa aplikasi, mungkin terjadi error rendering atau data kosong'
                    ];
                    $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                                     $analysis['reason'], $analysis['detail'], $analysis['action']);
                    return;
                }

                // 🔥 CEK APAKAH HALAMAN TIDAK PUNYA KONTEN BERMAKNA
                if (!$contentAnalysis['has_content']) {
                    $analysis = [
                        'status' => 'WARNING',
                        'reason' => 'NO_MEANINGFUL_CONTENT',
                        'detail' => '⚠️ Halaman tidak memiliki konten bermakna (hanya tag HTML kosong)',
                        'action' => '📄 Periksa aplikasi, mungkin halaman error atau maintenance'
                    ];
                    $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                                     $analysis['reason'], $analysis['detail'], $analysis['action']);
                    return;
                }

                // 🔥 HALAMAN NORMAL DENGAN KONTEN
                $detail = $this->buildContentDetail($contentAnalysis);
                
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

            // JIKA RESPONSE BUKAN 200
            $analysis = $this->analyzeResponse($code, $time);

        } catch (\Exception $e) {
            $time = 0;
            $code = 'N/A';
            $analysis = $this->analyzeException($e->getMessage());
        }

        $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                         $analysis['reason'], $analysis['detail'], $analysis['action']);
    }

    /**
     * 🔥 ANALISIS LENGKAP KONTEN HALAMAN
     * Mendeteksi: gambar, video, teks, link, dan konten bermakna
     */
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

        // 1. 🔍 CEK GAMBAR
        preg_match_all('/<img[^>]+>/i', $body, $imgMatches);
        $result['image_count'] = count($imgMatches[0]);
        $result['has_images'] = $result['image_count'] > 0;

        // 2. 🔍 CEK VIDEO (tag video, iframe youtube, vimeo)
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

        // 3. 🔍 CEK TEKS (minimal 50 karakter)
        $text = strip_tags($body);
        $text = preg_replace('/\s+/', '', $text);
        $result['text_length'] = strlen($text);
        $result['has_text'] = $result['text_length'] > 50;

        // 4. 🔍 CEK LINK
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $body, $linkMatches);
        $result['link_count'] = count($linkMatches[0]);
        $result['has_links'] = $result['link_count'] > 0;

        // 5. 🔍 KESIMPULAN ADA KONTEN ATAU TIDAK
        $result['has_content'] = $result['has_images'] || 
                                 $result['has_videos'] || 
                                 $result['has_text'] || 
                                 $result['has_links'];

        // 6. 📝 BUAT DETAIL
        if ($result['has_images']) {
            $result['details'][] = "🖼️ {$result['image_count']} gambar";
        }
        if ($result['has_videos']) {
            $result['details'][] = "🎬 {$result['video_count']} video";
        }
        if ($result['has_text']) {
            $result['details'][] = "📝 " . number_format($result['text_length']) . " karakter teks";
        }
        if ($result['has_links']) {
            $result['details'][] = "🔗 {$result['link_count']} link";
        }

        return $result;
    }

    /**
     * 🔥 BUILD DETAIL KONTEN UNTUK PESAN
     */
    private function buildContentDetail($analysis)
    {
        if (empty($analysis['details'])) {
            return '⚠️ Halaman tidak memiliki konten yang terdeteksi';
        }

        $detail = '✅ Halaman memiliki konten: ' . implode(', ', $analysis['details']);
        
        // Tambahan informasi
        if ($analysis['has_images'] && !$analysis['has_text']) {
            $detail .= ' (⚠️ Hanya gambar, tidak ada teks)';
        }
        
        if ($analysis['has_text'] && !$analysis['has_images'] && !$analysis['has_videos']) {
            $detail .= ' (📄 Hanya teks, tidak ada media)';
        }

        return $detail;
    }

    /**
     * Cek apakah response body benar-benar kosong
     */
    private function isEmptyResponse($body)
    {
        $cleaned = preg_replace('/\s+/', '', strip_tags($body));
        return strlen($cleaned) === 0;
    }

    private function checkPing(Service $service)
    {
        // ... kode checkPing tetap sama ...
        $oldStatus = $service->last_status;

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
            $action = '📡 Cek koneksi jaringan atau pastikan device menyala';
        }

        $this->saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action);
    }

    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail
        ]);

        ServiceLog::create([
            'service_id' => $service->id,
            'status' => $status,
            'response_code' => $code,
            'response_time' => $time,
            'message' => $detail,
            'action' => $action
        ]);

        if ($oldStatus != $status) {
            $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
        }
    }

    // ==================== ANALYZE RESPONSE ====================
    private function analyzeResponse($code, $time)
    {
        if ($code == 200) {
            if ($time > 3) {
                return [
                    'status' => 'WARNING',
                    'reason' => 'SLOW_RESPONSE',
                    'detail' => "Response lambat ({$time}s)",
                    'action' => '🐌 Optimasi performa server (cache, database, kode)'
                ];
            }
            return [
                'status' => 'UP',
                'reason' => 'OK',
                'detail' => 'Service normal',
                'action' => '-'
            ];
        }

        if ($code == 301 || $code == 302) {
            return [
                'status' => 'UP',
                'reason' => "HTTP_{$code}",
                'detail' => $code == 301 ? 'Moved Permanently' : 'Found - Redirect sementara',
                'action' => $code == 301 ? '🔄 Update URL endpoint' : '🔍 Periksa redirect'
            ];
        }

        if ($code == 401) {
            return [
                'status' => 'UP',
                'reason' => 'HTTP_401',
                'detail' => 'Unauthorized - Perlu login/autentikasi',
                'action' => '🔐 Pastikan kredensial (API Key/Token) benar'
            ];
        }

        if ($code == 403) {
            return [
                'status' => 'UP',
                'reason' => 'HTTP_403',
                'detail' => 'Forbidden - Akses ditolak oleh server',
                'action' => '🔑 Periksa izin akses, API Key, atau IP whitelist'
            ];
        }

        if ($code == 404) {
            return [
                'status' => 'WARNING',
                'reason' => 'HTTP_404',
                'detail' => 'Not Found - Halaman/endpoint tidak ditemukan',
                'action' => '🔍 Periksa URL endpoint, pastikan path benar'
            ];
        }

        if ($code == 405) {
            return [
                'status' => 'WARNING',
                'reason' => 'HTTP_405',
                'detail' => 'Method Not Allowed - Method HTTP tidak diizinkan',
                'action' => '🔧 Ganti method HTTP (GET/POST/PUT) sesuai endpoint'
            ];
        }

        if ($code == 429) {
            return [
                'status' => 'WARNING',
                'reason' => 'HTTP_429',
                'detail' => 'Too Many Requests - Server overload atau rate limit',
                'action' => '⏳ Kurangi frekuensi request atau tambah interval'
            ];
        }

        if ($code == 500) {
            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_500',
                'detail' => 'Internal Server Error - Error di sisi server',
                'action' => '💥 Periksa log error server, cek kode aplikasi'
            ];
        }

        if ($code == 502) {
            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_502',
                'detail' => 'Bad Gateway - Proxy/gateway menerima respons invalid',
                'action' => '🌉 Periksa proxy, load balancer, atau gateway'
            ];
        }

        if ($code == 503) {
            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_503',
                'detail' => 'Service Unavailable - Server maintenance atau overload',
                'action' => '🔧 Cek maintenance server, scale up resource'
            ];
        }

        if ($code == 504) {
            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_504',
                'detail' => 'Gateway Timeout - Proxy/gateway timeout',
                'action' => '⏱️ Cek performa server, optimasi response time'
            ];
        }

        return [
            'status' => 'WARNING',
            'reason' => 'HTTP_ERROR',
            'detail' => "HTTP {$code} - Kode tidak dikenal",
            'action' => '🔧 Periksa dokumentasi API atau hubungi admin'
        ];
    }

    private function analyzeException($message)
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'timed out')) {
            return [
                'reason' => 'TIMEOUT',
                'detail' => 'Server terlalu lama merespon (timeout)',
                'action' => '⏱️ Cek performa server, optimasi query, tambah timeout'
            ];
        }

        if (str_contains($msg, 'connection refused')) {
            return [
                'reason' => 'CONNECTION_REFUSED',
                'detail' => 'Koneksi ditolak - Port tidak terbuka',
                'action' => '🔌 Server mati, service belum berjalan, atau firewall blocking'
            ];
        }

        if (str_contains($msg, 'could not resolve')) {
            return [
                'reason' => 'DNS_ERROR',
                'detail' => 'DNS tidak ditemukan - Domain tidak terdaftar',
                'action' => '🌐 Periksa DNS, domain, atau koneksi internet'
            ];
        }

        if (str_contains($msg, 'ssl') || str_contains($msg, 'certificate')) {
            return [
                'reason' => 'SSL_ERROR',
                'detail' => 'SSL/TLS Certificate Error - Sertifikat tidak valid',
                'action' => '🔒 Periksa sertifikat SSL, perbarui jika expired'
            ];
        }

        if (str_contains($msg, 'no route to host')) {
            return [
                'reason' => 'NO_ROUTE_TO_HOST',
                'detail' => 'Tidak ada route ke host - Jaringan terputus',
                'action' => '🌐 Cek koneksi jaringan, firewall, atau routing'
            ];
        }

        if (str_contains($msg, 'network is unreachable')) {
            return [
                'reason' => 'NETWORK_UNREACHABLE',
                'detail' => 'Jaringan tidak dapat dijangkau',
                'action' => '🌐 Cek koneksi internet dan jaringan lokal'
            ];
        }

        return [
            'reason' => 'UNKNOWN',
            'detail' => $message,
            'action' => '🔧 Periksa service secara manual, cek log server'
        ];
    }

    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();

        if ($status == 'DOWN') {
            $message = 
"🔴 SERVICE DOWN

📌 Nama Service : {$service->name}
🔗 URL : {$service->target}

⚠️ Status : DOWN
📟 Code : {$code}

❌ Penyebab :
{$reason}

💡 Detail :
{$detail}

🔧 Tindakan :
{$action}

⏱️ Response :
{$time}s

🕐 " . now()->format('d-m-Y H:i:s');

        } elseif ($status == 'WARNING') {
            $message = 
"🟠 SERVICE WARNING

📌 Nama Service : {$service->name}
🔗 URL : {$service->target}

📟 Code : {$code}

❌ Penyebab :
{$reason}

💡 Detail :
{$detail}

🔧 Tindakan :
{$action}

⏱️ Response :
{$time}s

🕐 " . now()->format('d-m-Y H:i:s');

        } else {
            $message = 
"🟢 SERVICE NORMAL

📌 Nama Service : {$service->name}
🔗 URL : {$service->target}

✅ Status : UP
📟 Code : {$code}
⏱️ Response : {$time}s

🕐 " . now()->format('d-m-Y H:i:s');
        }

        foreach ($contacts as $contact) {
            FonnteService::send($contact->phone, $message);
        }
    }
}