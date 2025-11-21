<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $url;
    protected string $token;
    protected string $defaultGroupId;

    public function __construct()
    {
        $this->url = config('services.whatsapp.url');         // e.g. https://app.fastwa.com/api/v1/…/send_group
        $this->token = config('services.whatsapp.token');
        $this->defaultGroupId = config('services.whatsapp.group_id');
    }

    /**
     * Kirim pesan ke group WhatsApp.
     *
     * @param string|null $groupId
     * @param string      $message
     * @return bool
     */
    public function sendGroupMessage(?string $groupId, string $message): bool
    {
        $groupId = $groupId ?: $this->defaultGroupId;

        // Simulasi jika environment local
        if (config('app.env') === 'local') {
            Log::info("[LOCAL MODE] WhatsApp simulated send to group {$groupId}");
            Log::info("Message:\n{$message}");
            return true;
        }

        // Sesuaikan endpoint dan parameter sesuai dokumentasi FastWA.
        // Misalnya endpoint: /send_group atau /send_text_group
        $response = Http::asForm()->withOptions([
            'verify' => 'C:/xampp/apache/bin/curl-ca-bundle.crt',
        ])->post($this->url, [
            'api_key'  => $this->token,
            'phone' => $groupId,     // note: gunakan key 'group_id' jika itu parameter yang diminta
            'message'  => $message,
        ]);

        if (!$response->successful()) {
            Log::error("WhatsApp send to group failed", [
                'status' => $response->status(),
                'body'   => $response->body(),
                'groupId'=> $groupId,
            ]);
        } else {
            Log::info("WhatsApp message to group sent successfully", [
                'groupId' => $groupId,
                'message' => $message,
            ]);
        }

        return $response->successful();
    }
}
