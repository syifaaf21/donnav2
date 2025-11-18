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
        $this->groupId = config('services.whatsapp.group_id');
    }

    /**
     * Kirim pesan ke group WhatsApp
     *
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $message): bool
    {

        // STEP 1: Cek environment local
        if (config('app.env') === 'local') {
            Log::info("[LOCAL MODE] WhatsApp simulated send to group {$this->groupId}");
            Log::info("Message:\n{$message}");
            return true;
        }

        try {
            // STEP 2: Kirim request ke RuangWA dengan opsi verify=false
            $response = Http::asForm()->withOptions([
                'verify' => false, // skip SSL verification
            ])->post($this->url, [
                'token' => $this->token,
                'number' => $this->groupId,
                'message' => $message,
            ]);

            // STEP 3: Log response untuk debugging
            Log::info("RuangWA response status: " . $response->status());
            Log::info("RuangWA response body: " . $response->body());

            $body = json_decode($response->body(), true);

            // STEP 4: Cek apakah API berhasil
            return isset($body['success']) && $body['success'] === true;
        } catch (\Exception $e) {
            Log::error("Exception sending WhatsApp message", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
