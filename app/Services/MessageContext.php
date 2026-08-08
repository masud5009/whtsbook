<?php

namespace App\Services;

use App\Models\User\BotShareInfo;
use Illuminate\Support\Facades\DB;
use App\Models\User\AiKnowledgeVault;
use Illuminate\Support\Facades\Cache;

class MessageContext
{
    protected $aiService;

    protected $roomDetailsService;

    public function __construct()
    {
        $this->aiService = new AiService;
        $this->roomDetailsService = new RoomDetailsService;
    }

    /**
     * Analyze customer message and get the context with AI
     */
    public function analyzeContextWithAI($message, $user_id, $customerPhone, $customerProfileName, $wp)
    {
        $roomDetailsService = new RoomDetailsService;
        $response = ContextHandeler::getContext($user_id, $customerPhone, $message, $wp, $customerProfileName);

        if (! is_array($response)) {
            return $response ?: response_from_admin($wp->id, 'system_fallback');
        }

        if ($response['intent'] == null) {
            return response_from_admin($wp->id, 'system_fallback');
        }

        $wp_id = $wp->id;
        $slots = $response['slots'] ?? [];
        $intent = $response['intent'] ?? 'greeting';

        // Extract required custom booking fields from WhatsApp configuration
        $custom_fields = json_decode($wp->custom_booking_fields, true);
        $required_custom_fileds = [];
        if ($custom_fields) {
            if (is_array($custom_fields)) {
                foreach ($custom_fields as $field) {
                    if (! empty($field['label']) && isset($field['required']) && $field['required'] === true) {
                        $required_custom_fileds[] = $field['label'];
                    }
                }
            }
        }
        // Check if all required custom booking fields are present in the slots
        $all_required_present = true;
        $customer_custom_fields = $slots['custom_fields'] ?? [];
        foreach ($required_custom_fileds as $label) {
            // Check if the label exists as a key in $customer_custom_fields and the value is not empty (maybe trim)
            if (! isset($customer_custom_fields[$label]) || empty(trim($customer_custom_fields[$label]))) {
                $all_required_present = false;
                break;
            }
        }

        // check all basic slots are filled
        $basicSlotsIsFilled = ! is_null($slots['check_in_date']) && ! is_null($slots['check_out_date']) && ! is_null($slots['adults']) && ! is_null($slots['children']);

        // get room images
        if ($intent == 'view_images' && $basicSlotsIsFilled && ! empty($slots['details_room_title'])) {
            return $roomDetailsService->roomImageLink($slots['details_room_title'], $customerPhone, $user_id);
        }

        // hotel_information
        if ($intent == 'hotel_information') {
            $knowledgeContext = Cache::remember(
                $this->hotelKnowledgeCacheKey((int) $wp->user_id),
                now()->addHours(24), // 30 min → 24 hours
                function () use ($wp) {
                    $vault = AiKnowledgeVault::query()
                        ->where('user_id', $wp->user_id)
                        ->latest('id')
                        ->first();

                    return $this->buildHotelKnowledgeContext($vault);
                }
            );

            // Hotel information for prompt
            $shareInfo = BotShareInfo::where('user_id', $user_id)
                ->select('hotel_name', 'email_address', 'phone_numbers', 'locations')
                ->first();
            $hotelEmail = ! empty($shareInfo->email_address) ? (is_array($shareInfo->email_address)
                ? implode(', ', $shareInfo->email_address)
                : $shareInfo->email_address) : '';
            $hotelPhone = ! empty($shareInfo->phone_numbers) ? (is_array($shareInfo->phone_numbers)
                ? implode(', ', $shareInfo->phone_numbers)
                : $shareInfo->phone_numbers) : '';
            $hotelName = ! empty($shareInfo->hotel_name) ? $shareInfo->hotel_name : '';

            // Hotel settings for prompt
            $bs = DB::table('user_basic_settings')->where('user_id', $user_id)
                ->select(

                    'checkin_time',
                    'checkout_time',
                    'room_booking_cancellation',
                    'cancellation_time_limit_hours',
                    'cancellation_refund_percentage',
                    'timezone'
                )
                ->first();
            $checkinTime = $bs->checkin_time ?? '';
            $checkoutTime = $bs->checkout_time ?? '';
            $bookingCancellation = (int) ($bs->room_booking_cancellation ?? 0);
            $cancellationHours = (int) ($bs->cancellation_time_limit_hours ?? 0);
            $cancellationRefundPct = (int) ($bs->cancellation_refund_percentage ?? 0);

            $hotelInfoPrompt = <<<EOT
You are a friendly human hotel receptionist at {$hotelName}. Never say you are a bot, AI, or chatbot.
- Always reply in the SAME language the customer used
- Use a warm, helpful, and natural tone — like a real hotel staff member
- Answer based ONLY on the HOTEL KNOWLEDGE provided
- Hotel settings: check-in={$checkinTime}, check-out={$checkoutTime}, cancellation={$bookingCancellation}, cancellation_hours={$cancellationHours}, refund={$cancellationRefundPct}%
- If the information is not in the knowledge, respond naturally as a receptionist would — say something like "I'll have to check on that for you" or "For that, it would be best to reach us directly" then naturally mention: phone: {$hotelPhone}, email: {$hotelEmail}. Never list them robotically.
- Keep reply short (max 2-3 sentences). No bullet points. No markdown.


Customer question: {$message}
HOTEL KNOWLEDGE: {$knowledgeContext}
EOT;

            $aiReply = $this->aiService->getAiResponse($hotelInfoPrompt, $wp->user_id);

            return $aiReply ?? $response['required_response'] ?? response_from_admin($wp_id, 'system_fallback');
        }

        // get available rooms
        if ($intent == 'show_rooms' && $basicSlotsIsFilled) {
            // When searching with different guest parameters, clear previous room selections
            // so customer picks from the newly available rooms only
            if (! empty($slots['room_titles'])) {
                // Check if ANY room from old selection matches new guest requirements
                $roomResponse = $roomDetailsService->getAvailableRooms($slots, $user_id, $customerPhone);

                // Clear old room selections to force fresh choice from new list
                $cacheKey = 'context_slots_' . $customerPhone . '_' . $user_id;
                $context = Cache::get($cacheKey, []);
                $context['slots']['room_titles'] = [];
                $context['slots']['details_room_title'] = null;
                $context['slots']['room_quantities'] = [];
                Cache::put($cacheKey, $context, now()->addDays(30));

                return $roomResponse;
            }
            if (empty($slots['room_titles']) && empty($slots['available_room_titles'])) {
                return $roomDetailsService->getAvailableRooms($slots, $user_id, $customerPhone);
            }

            return $response['required_response'] ?? response_from_admin($wp_id, 'system_fallback');
        }
        // get room details
        elseif ($intent == 'specific_rooms') {
            $specificRoomResponse = $roomDetailsService->getSpecificRooms($slots['details_room_title'], $customerPhone, $user_id);

            if ($specificRoomResponse == 'room_not_found') {
                return $response['required_response'] ?? response_from_admin($wp_id, 'system_fallback');
            } else {
                return $specificRoomResponse;
            }
        }
        // store booking if intent is confirm_booking and all required custom fields + basic slots are present
        elseif ($intent == 'confirm_booking' && $all_required_present == true) {
            $booking = Common::storeBooking($slots, $user_id, $customerPhone, $wp_id);

            if ($booking['status'] === 'success') {
                return response_from_admin($wp_id, 'booking_placed');
            } elseif ($booking['status'] === 'room_not_found') {
                $prompt = 'Same language as: "' . $message . '". Tell user to provide correct room name or say "show rooms" to see available rooms. One short friendly sentence.';

                $aiReply = $this->aiService->getAiResponse($prompt, $user_id, []);

                return ! empty($aiReply)
                    ? trim($aiReply)
                    : "Please tell me the exact room name or say 'show rooms' to see available rooms.";
                // return "Please tell me the exact room name you would like to book. If you want to see the available rooms again, just let me know. Or want to available rooms just say 'show rooms'.";
            } elseif ($booking['status'] === 'error') {
                return $booking['message'];
            } else {
                return response_from_admin($wp_id, 'system_fallback');
            }
        } elseif ($intent == 'confirm_booking' && $all_required_present == false) {
            // Ask for all missing required custom fields at once
            $missing_fields = [];
            foreach ($required_custom_fileds as $label) {
                if (! isset($customer_custom_fields[$label]) || empty(trim($customer_custom_fields[$label]))) {
                    $missing_fields[] = $label;
                }
            }
            if (! empty($missing_fields)) {
                $fieldsText = implode(', ', $missing_fields);
                $prompt = "Reply in the same language as this message: \"{$message}\". Ask the user to provide all of these fields at once in one friendly sentence: {$fieldsText}. Keep it short.";
                $aiReply = $this->aiService->getAiResponse($prompt, $wp->user_id);

                return ! empty($aiReply)
                    ? trim($aiReply)
                    : "To confirm your booking, please provide the following information: {$fieldsText}.";
            }
        }
        // For other intents + ask for information if required fields are missing
        else {
            return $response['required_response'] ?? response_from_admin($wp_id, 'system_fallback');
        }
    }

    private function buildHotelKnowledgeContext(AiKnowledgeVault $vault)
    {
        if (! $vault) {
            return 'No stored hotel knowledge.';
        }
        $extract = trim((string) ($vault->extracted_text ?? ''));

        if ($extract === '') {
            return 'No usable hotel knowledge stored.';
        }

        return $extract;
    }

    private function hotelKnowledgeCacheKey(int $userId): string
    {
        return 'ai_knowledge_context_' . $userId;
    }
}
