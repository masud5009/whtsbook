<?php

namespace App\Http\Helpers;

use App\Models\User\Whatsapp;
use Illuminate\Support\Facades\Http;

class WhatsappSender
{
    public static function sendMessage($wp_id, $to, $text)
    {
        $wp = Whatsapp::where('id', $wp_id)
            ->where('status', 1)
            ->first();

        $url = "https://graph.facebook.com/v22.0/{$wp->wp_phone_number}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text]
        ];

        try {
            $response = Http::withToken($wp->wp_access_token)
                ->withOptions(['verify' => false, 'timeout' => 15])
                ->post($url, $payload);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
