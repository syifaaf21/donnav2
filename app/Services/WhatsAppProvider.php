<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $response = Http::asForm()->post($this->url, [
            'token' => $this->token,
            'number' => $number,
            'message' => $message,
        ]);

        return $response->successful();
    }
}
