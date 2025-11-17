<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $url;
    protected $token;
    protected $groupId;

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

        //FastWA for production
        // $response = Http::asForm()->post($this->url, [
        //     'api_key' => $this->token,
        //     'phone'   => $number,
        //     'message' => $message,
        // ]);

        //FastWA for tetsing
         $response = Http::asForm()->withOptions([
            'verify' => 'C:/xampp/apache/bin/curl-ca-bundle.crt'
        ])->post($this->url, [
            'api_key' => $this->token,
            'phone'   => $number,
            'message' => $message,
        ]);

        //RuangWA for Production
        // $response = Http::asForm()->post($this->url, [
        //     'token'   => $this->token,
        //     'id'      => $this->groupId,
        //     'message' => $message,
        // ]);

        return $response->successful();
    }
}
