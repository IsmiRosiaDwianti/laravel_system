<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public static function send($target, $message)
    {
        try {

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => env('FONNTE_TOKEN')
                ])
                ->post(
                    'https://api.fonnte.com/send',
                    [
                        'target' => $target,
                        'message' => $message,
                    ]
                );

            Log::info('Fonnte Success', [

                'target' => $target,

                'status_code' => $response->status(),

                'response' => $response->json()

            ]);

            return $response;

        } catch (\Exception $e) {

            Log::error('Fonnte Error', [

                'target' => $target,

                'error' => $e->getMessage()

            ]);

            return false;
        }
    }
}