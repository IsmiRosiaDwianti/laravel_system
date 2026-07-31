<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// 🔥 CEK INTERNET - MULTI METHOD
// ============================================================
function isInternetReallyConnected(): bool
{
    // 🔥 METHOD 1: DNS (TERCEPAT)
    $dnsTargets = ['google.com', 'cloudflare.com', 'microsoft.com'];
    foreach ($dnsTargets as $target) {
        if (checkdnsrr($target, 'A')) {
            Log::info('🌐 [DNS] Internet: ONLINE via ' . $target);
            return true;
        }
    }
    
    // 🔥 METHOD 2: HTTP (FALLBACK)
    try {
        $targets = [
            'https://www.google.com',
            'https://www.cloudflare.com',
            'https://www.microsoft.com'
        ];
        
        foreach ($targets as $target) {
            if (function_exists('curl_init')) {
                $ch = curl_init($target);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode > 0 && $httpCode < 500) {
                    Log::info('🌐 [CURL] Internet: ONLINE via ' . $target);
                    return true;
                }
            }
        }
    } catch (\Exception $e) {
        Log::info('❌ [CURL] Error: ' . $e->getMessage());
    }
    
    Log::info('⏸️ [CHECK] Internet: OFFLINE');
    return false;
}

// ============================================================
// 🚀 SERVICE MONITOR - MATI SAAT INTERNET DOWN
// ============================================================
Schedule::command('monitor:services')
    ->everyMinute()
    ->withoutOverlapping()
    ->name('monitor-services')
    ->runInBackground()
    ->skip(function () {
        $isConnected = isInternetReallyConnected();
        if (!$isConnected) {
            Log::info('⏸️ [SCHEDULE] Internet DOWN - monitor:services SKIPPED');
        } else {
            Log::info('🌐 [SCHEDULE] Internet OK - monitor:services RUNNING');
        }
        return !$isConnected;
    });

// ============================================================
// 🚀 SMOKE/ESP MONITOR - TETAP JALAN
// ============================================================
Schedule::command('app:check-smoke-devices')
    ->everyMinute()
    ->withoutOverlapping()
    ->name('check-smoke-devices')
    ->runInBackground();