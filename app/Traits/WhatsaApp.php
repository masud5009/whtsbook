<?php

namespace App\Traits;

use App\Models\User\Whatsapp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait WhatsaApp
{
    /**
     * Get Whatsapp Business Account All Information
     */
    private function getWhatsapp($phoneId)
    {
        $whatsap =   Whatsapp::query()
            ->where(function ($q) use ($phoneId) {
                $q->where('wp_phone_number', $phoneId)
                    ->orWhere('wp_from_number', $phoneId);
            })
            ->where('status', 1)
            ->first();

        if ($whatsap) return $whatsap;

        return null;
    }


    private function tokenLimitCheck($phoneId)
    {
        $whatsapp = $this->getWhatsapp($phoneId);
        if (!$whatsapp) return false;

        $usage = DB::table('ai_useages')
            ->where('user_id', $whatsapp->user_id)
            ->first();

        if (!$usage) return false;

        $used  = (int) $usage->total_tokens;
        $limit = (int) $usage->total_usable_tokens
            + (int) ($usage->extend_token ?? 0);

        return $used < $limit;
    }

    /**
     * Send typing indicator to show bot is processing
     */
    private function sendTypingIndicator(Whatsapp $wp, string $messageId): void
    {
        $url = "https://graph.facebook.com/v25.0/{$wp->wp_phone_number}/messages";

        try {
            $response = Http::withToken($wp->wp_access_token)
                ->withOptions(['verify' => false, 'timeout' => 5])
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'status' => 'read',
                    'message_id' => $messageId,
                    'typing_indicator' => [
                        'type' => 'text',
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Typing indicator failed', [
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Typing indicator failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send reply using WhatsApp API
     */
    private function sendReply($wp, $to, $text)
    {
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

            if (!$response->successful()) {
                Log::error('WhatsApp API Error Response:', [
                    'message' => $response->json('error.message')
                ]);
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp connection failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
