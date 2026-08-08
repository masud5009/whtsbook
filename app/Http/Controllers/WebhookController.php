<?php

namespace App\Http\Controllers;

use App\Services\Common;
use App\Traits\WhatsaApp;
use Illuminate\Http\Request;
use App\Models\User\Whatsapp;
use App\Services\MessageContext;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Helpers\LimitCheckerHelper;

class WebhookController extends Controller
{
    use WhatsaApp;
    /**
     * Verify WhatsApp webhook
     */
    public function verifyWebhook(Request $request)
    {
        // Support both Meta standard dot params and underscore variants
        $mode = $request->query('hub.mode') ?? $request->query('hub_mode');
        $token = $request->query('hub.verify_token') ?? $request->query('hub_verify_token');
        $challenge = $request->query('hub.challenge') ?? $request->query('hub_challenge');

        if ($mode !== 'subscribe' || !$token || !$challenge) {
            return response('Invalid verification request', 400);
        }

        $wp = Whatsapp::where('wp_verify_token', $token)->first();
        if (!$wp) {
            return response('Forbidden', 403);
        }

        return response($challenge, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Handle WhatsApp webhook - Process inline (no queue)
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->json()->all();

        $change = data_get($payload, 'entry.0.changes.0.value');
        if (!is_array($change)) {
            return response('Ignored: invalid payload', 200);
        }

        $message = $change['messages'][0] ?? null;
        $phoneId = data_get($change, 'metadata.phone_number_id');
        if (!$message || !$phoneId) return response('Ignored: no message', 200);

        $from = $message['from'] ?? null;
        $messageId = $message['id'] ?? null;
        if (!$from || !$messageId) return response('ignored missing from/message_id', 200);

        // Atomic duplicate prevention - Cache::add() returns false if key already exists
        // whats_app_chats is only written on failure, so the old DB check never blocked retries
        $dedupKey = 'whatsapp_msg_' . $messageId;
        if (!Cache::add($dedupKey, 1, now()->addMinutes(30))) {
            return response('Already processed', 200);
        }

        //If message is not text
        $text = $message['text']['body'] ?? null;
        if (($message['type'] ?? '') !== 'text' || $text === '') {
            $wp = $this->getWhatsapp($phoneId);
            $this->sendReply($wp, $from, 'Allow only text messages');
            return response('ignored: not text message', 200);
        }

        //If message is too long
        if (mb_strlen($text, 'UTF-8') > 500) {
            $wp = $this->getWhatsapp($phoneId);
            $this->sendReply($wp, $from, 'Message too long (max 500 characters).');
            return response('ignored: message too long', 200);
        }

        $contactData = $change['contacts'][0] ?? [];
        $profileName = $contactData['profile']['name'] ?? null;

        // Process webhook inline
        $this->processMessage($phoneId, $messageId, $from, $text, $profileName);

        return response('OK', 200);
    }

    /**
     * Process WhatsApp message inline
     */
    private function processMessage($phoneId, $messageId, $from, $text, $profileName)
    {
        try {
            $wp = $this->getWhatsapp($phoneId);
            if ($wp === null) return;

            if ($this->tokenLimitCheck($phoneId) === false) {
                $this->sendReply($wp, $from, "Currently our system is unavailable. Please try again later.");
                return;
            }

            $userId = $wp->user_id;
            $checkLimit = LimitCheckerHelper::roomBookingCountUser($userId) < LimitCheckerHelper::roomBookingsLimit($userId);
            if (!$checkLimit) {
                $this->sendReply($wp, $from, "Currently our system is unavailable. Please try again later.");
                return;
            }

            $customerMessageInfos = [
                'message_id' => $messageId,
                'customer_phone' => $from,
                'customer_message' => $text,
                'phone_number_id' => $phoneId,
                'user_id' => $userId
            ];

            // Show typing indicator while processing
            $this->sendTypingIndicator($wp, $messageId);

            // AI Analysis with retry logic
            $maxRetries = 3;
            $retryDelay = 1;
            $response = null;
            $analysisCompleted = false;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $msgContext = new MessageContext();
                    $response = $msgContext->analyzeContextWithAI($text, $userId, $from, $profileName, $wp);
                    $analysisCompleted = true;
                    break;
                } catch (\Exception $e) {
                    if ($attempt === $maxRetries) {
                        break;
                    }
                    sleep($retryDelay);
                    $retryDelay *= 2;
                }
            }

            // If AI analysis completely failed, send fallback
            if (!$analysisCompleted) {
                $reply = response_from_admin($wp->id, 'system_fallback');
                $this->sendReply($wp, $from, $reply);
                return;
            }

            if (is_null($response)) {
                return;
            }

            // Send reply
            $replySend = $this->sendReply($wp, $from, $response);

            if (!$replySend) {
                Common::storeFailedMessage($response, $customerMessageInfos);
            }
        } catch (\Exception $e) {
            \Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($userId)) {
                Common::storeFailedMessage('Processing Error: ' . $e->getMessage(), $customerMessageInfos ?? []);
                $reply = response_from_admin($wp->id, 'system_fallback');
                $this->sendReply($wp, $from, $reply);
            }
        }
    }
}
