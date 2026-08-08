<?php

namespace App\Services;

use DateTime;
use DateTimeZone;
use App\Services\AiService;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\Cache;

class ContextHandeler
{
    /**
     * Analyze customer message and get the context with AI
     */
    public static function getContext($userId, $customerPhone, $message, $wp, $customerProfileName)
    {
        $cacheKey = 'context_slots_' . $customerPhone . '_' . $userId;

        // Extract custom field labels from WhatsApp configuration for prompt
        $custom_fields = json_decode($wp->custom_booking_fields, true) ?? [];
        $custom_field_names = [];
        foreach ($custom_fields as $field) {
            if (!empty($field['label'])) {
                $custom_field_names[] = $field['label'];
            }
        }

        // Get previous context from cache or initialize with default values
        $previousContext = Cache::get($cacheKey, [
            'intent' => null,
            'conversation_summary' => '',
            'slots' => [
                'check_in_date' => null,
                'check_out_date' => null,
                'adults' => null,
                'children' => null,
                'full_name' => null,
                'email' => null,
                'phone' => null,
                'room_titles' => [],
                'available_room_ids' => [],
                'available_room_titles' => [],
                'room_quantity' => '1',
                'details_room_title' => null,
                'room_quantities' => [],
                'custom_fields' => [],
            ],
            'required_response' => '',
        ]);

        // Strip internal DB IDs from prompt — AI doesn't need room IDs, saves tokens
        $promptContext = $previousContext;
        unset($promptContext['slots']['available_room_ids']);
        $previousContextJson = json_encode($promptContext, JSON_UNESCAPED_UNICODE);
        $customFieldText = !empty($custom_field_names) ? implode(', ', $custom_field_names) : 'None';
        $availableRoomTitles = $previousContext['slots']['available_room_titles'] ?? [];
        $availableRoomTitles = is_array($availableRoomTitles)
            ? array_values(array_filter(array_map('trim', $availableRoomTitles)))
            : [];
        $availableRoomTitlesJoined = !empty($availableRoomTitles) ? implode(', ', $availableRoomTitles) : 'None';

        $timezone = TimzeZoneService::getUserTimeZone($userId);
        $todayDate = (new DateTime('now', new DateTimeZone($timezone)))->format('Y-m-d');

        // Prepare the prompt for AI with the user message, custom field labels, and previous context
        $prompt = <<<EOT
You are a friendly hotel receptionist.Your task is to analyze the user's message and extract the intent and relevant information for booking a hotel room.

Extract booking info from: "{$message}"
Previous: {$previousContextJson}
Custom fields to collect: {$customFieldText}

Rules:
-Use a warm, helpful, and natural tone — like a real hotel staff member
-If available_room_titles list is not empty and user mentions a room name NOT in this list: [{$availableRoomTitlesJoined}], say that room is not available and ask them to pick one from this same list.
-Reply in the same language as this message:{$message}
-Intent from: greeting|show_rooms|booking_flow|booking_summary|confirm_booking|price_negotiation|hotel_information|specific_rooms|gratitude|view_images|
-If intent would be specific_rooms but show_rooms is empty, set intent=show_rooms, details_room_title=null ask the user to select a room first
-DATES MUST be YYYY-MM-DD format.
-Today is {$todayDate} in hotel timezone ({$timezone}). Do not return past dates.
-If user gives day-only dates (like "12 tarikh to 14 tarikh") without month/year, infer nearest future valid dates from today.
-If user asks to book "another one", "same room", or "book again", use the dates/guests from the `last_booking` object to populate the slots for a new booking.
-If user asks to book "the other one" or a room name from `previous_availability.available_room_titles` that is NOT in `last_booking.booked_room_titles`, understand they want to book a different room from the last-shown list. Use dates/guests from `last_booking` and set the `room_titles` slot to the requested room.
-Generate 1-line conversation_summary
-When all fields collected AND intent=booking_summary, show booking summary with confirmation question
-If required fields for the current intent group are missing, ask for ALL of them together in ONE natural friendly sentence in required_response (e.g. "Could you share your check-in date, check-out date, and number of guests?")
-Never ask fields from a later intent group before the current intent's fields are complete (e.g. do NOT ask full_name/email during show_rooms intent)
-If user already provided some required fields, ask only for the remaining missing ones — all at once
-Custom field values MUST go inside "custom_fields" object, NOT as separate slots
-confirm_booking only for clear immediate confirmation; after any deferment (kal/later/not now/pore), a lone "yes/hae" is not confirm_booking unless user says confirm now.
-If intent = greeting, generate a friendly welcome message in required_response using customer name
-If user asks to see room images/photos/pictures/videos (ANY language - image, photo, pic, pictureetc) → intent = view_images and set view_images=true in slots. If they haven't provided check-in/out dates, ask for those first in required_response.
-In booking_summary, display full_name as "Person Name" and room_titles/details_room_title as "Room name".
-booking_summary: flat list (Name,Room,Dates,Phone,Email,Adults,Children, each custom field as "Label: Value"). No "Custom Fields:" header. Then ask to confirm.
-children field: null = ask, 0 or more = do not ask
-confirm_booking: required_response empty
-If user refers to a room without stating exact name (e.g. "the cheapest", "luxury room"), ask them to specify the room name from available_room_titles. Do NOT guess or auto-fill room_titles.

Required fields per intent:
-show_rooms/booking_flow/specific_rooms: check_in_date, check_out_date, adults, children
-booking_summary/confirm_booking: + full_name,email,phone and all custom fields: {$customFieldText}.

Return JSON only:
{"intent": "","conversation_summary": "","slots": {"check_in_date": null,"check_out_date": null,"adults": null,"children": null,"full_name": null,"email": null,"phone": null,"available_room_ids": [],"available_room_titles": [],"room_titles": [],"room_quantity": "1","details_room_title": null,"room_quantities": {},"view_images": false,"custom_fields": {"field_label_1": "value",}},"required_response": ""}

Example for custom fields:
If custom fields = "passport_number, nationality"
And user says: "My passport is AB123456 and I'm from Bangladesh"
Then custom_fields should be: {"passport_number": "AB123456", "nationality": "Bangladesh"}
EOT;

        //Send the prompt to AI and get response
        $aiService = new AiService();
        $aiResponse = $aiService->getAiResponse($prompt, $userId, $previousContext);

        if (!$aiResponse) return response_from_admin($wp->id, 'system_fallback');

        // Clean and decode AI response
        $clean = trim($aiResponse);
        $clean = preg_replace('/```json|```/i', '', $clean);
        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) return $previousContext;

        // Merge previous context with new AI response
        $merged = self::mergeContext($previousContext, $decoded);

        // If intent is one of the booking steps but we don't have room info, force intent to show_rooms
        $forceToShowRooms = in_array($merged['intent'], ['booking_flow', 'booking_summary', 'confirm_booking']);
        if (empty($merged['slots']['available_room_ids']) && $forceToShowRooms) {
            $merged['intent'] = 'show_rooms';

            if (empty($merged['required_response'])) {

                $slots = $merged['slots'];

                $missing = [];

                if (empty($slots['check_in_date'])) $missing[] = 'check-in date';
                if (empty($slots['check_out_date'])) $missing[] = 'check-out date';
                if (empty($slots['adults'])) $missing[] = 'number of adults';
                if (is_null($slots['children'])) $missing[] = 'number of children';

                if (empty($slots['full_name'])) $missing[] = 'your name';
                if (empty($slots['email'])) $missing[] = 'your email';
                if (empty($slots['phone'])) $missing[] = 'your phone number';
                if (!empty($slots['custom_fields'])) {
                    foreach ($slots['custom_fields'] as $key => $value) {
                        if (empty($value)) {
                            $missing[] = $key;
                        }
                    }
                }

                // Only ask if something is missing
                if (!empty($missing)) {
                    $fieldsText = implode(', ', $missing);

                    $fallbackPrompt = 'Same language as: "' . $message . '". Ask for ' . $fieldsText . ' in one friendly sentence.';

                    $merged['required_response'] = trim(
                        $aiService->getAiResponse($fallbackPrompt, $userId, [])
                    );
                }
            }
        }

        // If intent is show_rooms but we don't have room info, force intent to show_rooms
        if (!empty($merged['slots']['available_room_ids']) && $forceToShowRooms && empty($merged['slots']['room_titles'])) {
            $merged['intent'] = 'show_rooms';

            if (empty($merged['required_response'])) {
                $roomTitles = $merged['slots']['available_room_titles'] ?? [];
                $roomList = !empty($roomTitles) ? implode(', ', $roomTitles) : 'the available rooms';

                $fallbackPrompt = 'Same language as: "' . $message . '". Ask the user to choose one room from this list: ' . $roomList . '. One friendly sentence.';
                $merged['required_response'] = trim(
                    $aiService->getAiResponse($fallbackPrompt, $userId, [])
                );
            }
        }

        // Save merged context back to cache for future messages
        Cache::put($cacheKey, $merged, now()->addDays(30));
        return $merged;
    }

    /**
     * Merge previous context with new AI response, giving precedence to new non-empty values, and combining custom fields appropriately.
     */
    protected static function mergeContext($previous, $current)
    {
        $previousSlots = $previous['slots'] ?? [];
        $currentSlots = $current['slots'] ?? [];

        $mergedSlots = $previousSlots;

        foreach ($currentSlots as $key => $value) {
            if ($key === 'custom_fields') {
                $mergedSlots['custom_fields'] = array_merge(
                    $previousSlots['custom_fields'] ?? [],
                    array_filter($value ?? [], function ($v) {
                        return $v !== null && $v !== '';
                    })
                );
                continue;
            }

            if (is_array($value)) {
                if (!empty($value)) {
                    $mergedSlots[$key] = $value;
                }
            } else {
                if ($value !== null && $value !== '') {
                    $mergedSlots[$key] = $value;
                }
            }
        }

        // Handle conversation summary - either use new one or keep previous
        $conversationSummary = $previous['conversation_summary'] ?? '';

        // If AI returned a new summary, use it
        if (isset($current['conversation_summary']) && !empty($current['conversation_summary'])) {
            $conversationSummary = $current['conversation_summary'];
        }
        // Otherwise, if this is a new intent and we have a previous summary, maybe append?
        else if (!empty($previous['conversation_summary']) && isset($current['intent']) && $current['intent'] !== $previous['intent']) {
            // Optionally append new info to existing summary
            // $conversationSummary = $previous['conversation_summary'] . " User is now " . $current['intent'];
        }

        return [
            'intent' => $current['intent'] ?? ($previous['intent'] ?? 'unknown'),
            'conversation_summary' => $conversationSummary,
            'slots' => $mergedSlots,
            'required_response' => $current['required_response'] ?? ($previous['required_response'] ?? ''),
        ];
    }
}
