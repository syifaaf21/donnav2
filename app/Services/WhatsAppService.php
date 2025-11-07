<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $url;
    protected $token;

    public function __construct()
    {
        $this->url = config('services.whatsapp.url');
        $this->token = config('services.whatsapp.token');
    }

    public function sendMessage($number, $message)
    {

        // ✅ STEP 1: Cek apakah environment "local"
        if (config('app.env') === 'local') {
            // Simulasi saja (tidak benar-benar kirim)
            Log::info("[LOCAL MODE] WhatsApp simulated send to {$number}");
            Log::info("Message:\n{$message}");
            echo "\n=============================\n";
            echo "[LOCAL MODE] WhatsApp message simulated!\n";
            echo "To: {$number}\n";
            echo "Message:\n{$message}\n";
            echo "=============================\n";

            return true; // anggap sukses
        }

        $response = Http::asForm()->post($this->url, [
            'token' => $this->token,
            'number' => $number,
            'message' => $message,
        ]);

        return $response->successful();
    }
}
