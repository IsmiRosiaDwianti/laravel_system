<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public static function send($target, $message)
    {
        try {
            // 🔥 Ambil API Key dari config, bukan langsung env()
            $apiKey = config('services.fonnte.api_key') ?? env('FONNTE_TOKEN');
            
            // 🔥 CEK APAKAH API KEY KOSONG
            if (empty($apiKey)) {
                Log::error('❌ FONNTE_TOKEN tidak ditemukan di .env atau config');
                return false;
            }
            
            Log::info('📤 Mengirim WA ke: ' . $target);
            Log::info('📝 Pesan: ' . substr($message, 0, 100) . '...');
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $apiKey
                ])
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);

            // 🔥 LOG RESPONSE LENGKAP
            Log::info('📊 Fonnte Response:', [
                'target' => $target,
                'status_code' => $response->status(),
                'success' => $response->successful(),
                'response' => $response->json()
            ]);

            // 🔥 RETURN BOOLEAN, BUKAN OBJECT
            if ($response->successful()) {
                Log::info('✅ WA berhasil dikirim ke: ' . $target);
                return true;
            } else {
                Log::error('❌ Gagal kirim WA ke: ' . $target . ' - Status: ' . $response->status());
                Log::error('❌ Response: ' . $response->body());
                return false;
            }

        } catch (\Exception $e) {
            Log::error('❌ Fonnte Error:', [
                'target' => $target,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}