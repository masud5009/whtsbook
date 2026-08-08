<?php

namespace App\Services;

use App\Models\User\Language;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Common
{
    /**
     * Store room booking
     */
    public static function storeBooking($data, $user_id, $customer_phone, $wp_id)
    {
        try {
            $languageId = Language::where('user_id', $user_id)
                ->where('is_default', 1)
                ->value('id');
            $showrooms = @$data['available_room_ids'] ?? []; //get room Ids which are shown to user in conversation
            //if no room shown
            if (count($showrooms) === 0) {
                return ['status' => 'room_not_found'];
            }

            // Validate that selected room(s) are from the current available list
            // (in case guest count changed and old room selection is no longer suitable)
            $selectedRoomIds = [];
            if (!empty($data['room_titles']) && is_array($data['room_titles'])) {
                $selectedRoomIds = DB::table('user_room_category_contents')
                    ->join('user_room_categories', 'user_room_category_contents.room_id', '=', 'user_room_categories.id')
                    ->where('user_room_categories.user_id', $user_id)
                    ->whereIn('user_room_category_contents.title', $data['room_titles'])
                    ->pluck('user_room_categories.id')
                    ->toArray();
            } elseif (!empty($data['details_room_title'])) {
                $selectedRoomIds[] = DB::table('user_room_category_contents')
                    ->join('user_room_categories', 'user_room_category_contents.room_id', '=', 'user_room_categories.id')
                    ->where('user_room_categories.user_id', $user_id)
                    ->where('user_room_category_contents.title', $data['details_room_title'])
                    ->value('user_room_categories.id');
                $selectedRoomIds = array_filter($selectedRoomIds);
            }

            // If selected room is not in the current available list, it means guest count or dates changed
            if (!empty($selectedRoomIds)) {
                $isValidSelection = false;
                foreach ($selectedRoomIds as $roomId) {
                    if (in_array($roomId, $showrooms)) {
                        $isValidSelection = true;
                        break;
                    }
                }
                if (!$isValidSelection) {
                    return [
                        'status' => 'error',
                        'message' => 'Your selected room is no longer available for the current guest count or dates. Please choose from the available rooms shown above.'
                    ];
                }
            }

            //if multiple rooms are shown
            if (count($showrooms) > 1) {
                $booking_response = BookingService::storeBookingForMultipleAvailableRooms($data, $user_id, $languageId, $customer_phone, $wp_id);
                if ($booking_response['status'] == 'room_not_found') return ['status' => 'room_not_found'];
                if (!is_null($booking_response) &&  $booking_response['status'] == 'error') {
                    return [
                        'status' => 'error',
                        'message' => $booking_response['message']
                    ];
                }
            }

            //if only one room is shown
            if (count($showrooms) === 1) {
                $booking_response = BookingService::storeBookingForSingleAvailableRooms($data, $user_id, $languageId, $customer_phone, $wp_id);

                if (!is_null($booking_response) && $booking_response['status'] == 'room_not_found') {
                    return ['status' => 'room_not_found'];
                }

                if (!is_null($booking_response) &&  $booking_response['status'] == 'error') {
                    return [
                        'status' => 'error',
                        'message' => $booking_response['message']
                    ];
                }
            }

            // Reset conversation context after successful booking.
            // Keeps name/email for convenience but clears booking-specific slots
            // (dates, room ids, etc.) so the next booking starts fresh.
            $cacheKey = 'context_slots_' . $customer_phone . '_' . $user_id;
            $existing = Cache::get($cacheKey, []);

            // Preserve the just-booked info and the list of rooms that were available
            $last_booking_details = [
                'check_in_date' => $existing['slots']['check_in_date'] ?? null,
                'check_out_date' => $existing['slots']['check_out_date'] ?? null,
                'adults' => $existing['slots']['adults'] ?? null,
                'children' => $existing['slots']['children'] ?? null,
                'booked_room_titles' => $data['room_titles'] ?? $data['details_room_title'] ?? null,
            ];

            $previous_availability_details = [
                'available_room_ids' => $existing['slots']['available_room_ids'] ?? [],
                'available_room_titles' => $existing['slots']['available_room_titles'] ?? [],
            ];

            Cache::put($cacheKey, [
                'intent' => null,
                'conversation_summary' => '',
                'slots' => [
                    'check_in_date' => null,
                    'check_out_date' => null,
                    'adults' => null,
                    'children' => null,
                    'full_name' => $existing['slots']['full_name'] ?? null,
                    'email' => $existing['slots']['email'] ?? null,
                    'available_room_ids' => [],
                    'available_room_titles' => [],
                    'room_titles' => [],
                    'room_quantity' => "1",
                    'details_room_title' => null,
                    'room_quantities' => [],
                    'view_images' => false,
                    'custom_fields' => [],
                ],
                'last_booking' => $last_booking_details, // Store details of the last successful booking
                'previous_availability' => $previous_availability_details, // Store the list of rooms shown before booking
                'required_response' => '',
            ], \Carbon\Carbon::now(\App\Services\TimzeZoneService::getUserTimeZone($user_id))->addDays(30)); // Keep this context for 30 days for follow-up questions

            return ['status' => 'success'];
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Get room title by room category ids for given user, considering user's default language
     */
    public static function getRoomTitleByRoomIds($user_id, $roomIds)
    {
        if (empty($roomIds) || !is_array($roomIds)) {
            return '';
        }
        $language = self::getUserDefaultLanguage($user_id);
        $rooms = DB::table('user_room_category_contents')
            ->join('user_room_categories', function ($join) use ($user_id) {
                $join->on('user_room_category_contents.room_id', '=', 'user_room_categories.id')
                    ->where('user_room_categories.user_id', $user_id);
            })
            ->where('user_room_category_contents.language_id', $language->id)
            ->where('user_room_categories.status', 1)
            ->whereIn('user_room_categories.id', $roomIds)
            ->select('user_room_category_contents.title')
            ->orderBy('user_room_category_contents.title')
            ->pluck('title')
            ->toArray();

        return implode(", ", $rooms);
    }

    /**
     * Get user's default language
     */
    public static function getUserDefaultLanguage($user_id)
    {
        return Language::where('user_id', $user_id)
            ->where('is_default', 1)
            ->first();
    }

    /**
     * Calculate price based on regular price, weekend price and seasonal price
     */
    public static function priceCalculation($room, $checkIn, $checkOut)
    {
        $regular_price = $room->regular_price;
        $weekend_price = $room->weekend_price;
        $seasonal_price = $room->seasonal_price;
        $seasonal_weekend_price = $room->seasonal_weekend_price;

        $dbWeekends = $room->weekend;
        $dbSeasonalDates = $room->seasonal_dates;
        $seasonalWeekends = $room->seasonal_weekend;

        $weekends = !empty($dbWeekends)
            ? array_values(array_filter(array_map('trim', explode(',', $dbWeekends))))
            : [];
        $sWeekends = !empty($seasonalWeekends)
            ? array_values(array_filter(array_map('trim', explode(',', $seasonalWeekends))))
            : [];

        $seasons = [];
        if (!empty($dbSeasonalDates)) {
            $decoded = json_decode($dbSeasonalDates, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $seasons = $decoded;
            }
        }

        $totalPrice = 0;
        $dailyDetails = [];

        $begin = new \DateTime($checkIn);
        $end = new \DateTime($checkOut);
        // $end->modify('+1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($begin, $interval, $end);

        foreach ($period as $date) {
            $currentDate = $date->format('Y-m-d');
            $dayName = $date->format('l');
            $todayPrice = $regular_price;
            $isSeasonalDate = false;

            if (!empty($seasons)) {
                foreach ($seasons as $range) {
                    if (!isset($range['start']) || !isset($range['end'])) continue;

                    if ($currentDate >= $range['start'] && $currentDate <= $range['end']) {
                        $isSeasonalDate = true;
                        if (in_array($dayName, $sWeekends)) {
                            $todayPrice = $seasonal_weekend_price ?? $seasonal_price ?? $regular_price;
                        } else {
                            $todayPrice = $seasonal_price ?? $regular_price;
                        }
                        break;
                    }
                }
            }

            if (!$isSeasonalDate) {
                if (in_array($dayName, $weekends)) {
                    $todayPrice = $weekend_price ?? $regular_price;
                }
            }

            $totalPrice += $todayPrice;
            $dailyDetails[] = [
                'date' => $currentDate,
                'day' => $dayName,
                'price' => $todayPrice
            ];
        }
        return [
            'totalPrice' => $totalPrice,
            'dailyDetails' => $dailyDetails,
            'totalDays' => count($dailyDetails)
        ];
    }

    /**
     * Store failed messages
     */
    public static function storeFailedMessage($botReply, array $customerMessageInfos): void
    {
        try {
            $exists = DB::table('whats_app_chats')
                ->where('message_id', $customerMessageInfos['message_id'])
                ->exists();

            if ($exists) {
                return;
            }

            DB::table('whats_app_chats')->insert([
                'message_id' => $customerMessageInfos['message_id'] ?? 'msg_fail_' . time() . '_' . rand(1000, 9999),
                'customer_phone' => $customerMessageInfos['customer_phone'],
                'user_id' => $customerMessageInfos['user_id'],
                'customer_name' => null,
                'direction' => 'incoming',
                'message_type' => 'text',
                'content' => json_encode([
                    'incoming_message' => $customerMessageInfos['customer_message'],
                    'failed_reply' => $botReply,
                    'phone_number_id' => $customerMessageInfos['phone_number_id'],
                    'user_id' => $customerMessageInfos['user_id'],
                ]),
                'status' => 'failed',
                'metadata' => json_encode([
                    'error' => 'Message send failed',
                    'timestamp' => now()->toDateTimeString(),
                    'reason' => 'WhatsApp API failed or processing error',
                ]),
                'received_at' => now(),
                'sent_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            return;
        }
    }
}
