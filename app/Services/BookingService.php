<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User\RoomBooking;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Get matched room titles based on user input and shown rooms in conversation, considering user's default language
     */
    public static function storeBookingForMultipleAvailableRooms($data, $user_id, $languageId, $customer_phone, $wp_id)
    {
        $showrooms = @$data['available_room_ids'] ?? []; //get room Ids which are shown to user in conversation

        //if user choose multiple rooms with quantity
        $roomQuantities = $data['room_quantities'] ?? [];
        if (!is_array($roomQuantities)) $roomQuantities = [];
        $roomTitles = array_keys($roomQuantities);

        //if room title notfound from room_quantities, then try to get room title if user mention with booking intent
        //without seeing room details
        if (empty($roomTitles)) {
            $roomTitles = $data['room_titles'];
        }

        //if room title not found from room_quantities and room_titles,
        //then try to get room title from details_room_title which is extracted when user mention specific room name in message
        if (empty($roomTitles)) {
            $roomTitles = $data['details_room_title'];
        }

        //normalize room titles to array
        if (!is_array($roomTitles)) {
            $roomTitles = [$roomTitles];
        }
        $roomTitles = array_values(array_filter(array_map('trim', $roomTitles)));

        $matchedTitles = [];
        $matchedQuantity = [];
        if (!empty($showrooms)) {
            //fetch shown room titles for matching
            $showroomsTitles = DB::table('user_room_category_contents')
                ->whereIn('room_id', $showrooms)
                ->where('language_id', $languageId)
                ->pluck('title')
                ->toArray();

            // Try to find exact or partial matches between shown room titles and input room titles
            foreach ($showroomsTitles as $dbTitle) {
                foreach ($roomTitles as $inputTitle) {
                    if ($inputTitle === '') continue;

                    if (stripos($dbTitle, $inputTitle) !== false || stripos($inputTitle, $dbTitle) !== false) {
                        $qty = isset($roomQuantities[$inputTitle]) ? (int)$roomQuantities[$inputTitle] : 1;
                        $matchedQuantity[$dbTitle] = $qty;
                        $matchedTitles[] = $dbTitle;
                        break;
                    }
                }
            }
            $matchedTitles = array_values(array_unique($matchedTitles));
        }

        $rooms = DB::table('user_room_category_contents')
            ->join('user_room_categories', 'user_room_category_contents.room_id', '=', 'user_room_categories.id')
            ->where('user_room_categories.user_id', $user_id)
            ->where('user_room_category_contents.language_id', $languageId)
            ->whereIn('user_room_category_contents.title', $matchedTitles)
            ->where('user_room_categories.status', 1)
            ->select(
                'user_room_categories.id as room_category_id',
                'user_room_categories.user_id',
                'user_room_category_contents.title as room_title',
                'user_room_category_contents.slug as room_slug'
            )->get();

        if ($rooms->isEmpty()) return ['status' => 'room_not_found'];

        //check room availability
        foreach ($rooms as $room) {
            $title = $room->room_title;
            $qty = $matchedQuantity[$title] ?? 1; //get quantity from matchedQuantity

            $availableCount = self::getAvailableRoomCount(
                $user_id,
                $room->room_category_id,
                $data['check_in_date'],
                $data['check_out_date']
            );
            if ($availableCount < $qty) {
                $roomName = $title ?: 'this room';
                $availableText = $availableCount > 0 ? "{$availableCount} available" : 'no availability';
                return [
                    'status' => 'error',
                    'message' => "Sorry, {$roomName} is not available for those dates ({$availableText}). Please choose another room."
                ];
            }
        }

        $timezone = TimzeZoneService::getUserTimeZone($user_id);
        $checkInDate = Carbon::parse($data['check_in_date'], $timezone)->format('Y-m-d');
        $checkOutDate = Carbon::parse($data['check_out_date'], $timezone)->format('Y-m-d');

        //get base currency
        $bs = DB::table('user_basic_settings')
            ->where('user_id', $user_id)
            ->select('base_currency_symbol', 'base_currency_symbol_position', 'base_currency_text', 'base_currency_text_position')
            ->first();

        //store bookings
        $createdBookings = [];
        foreach ($rooms as $room) {
            if (!$room || !isset($room->room_category_id)) {
                continue;
            }

            $title = $room->room_title;
            $qty = $matchedQuantity[$title] ?? 1; //get quantity from matchedQuantity

            $booking =  RoomBooking::create([
                'booking_number' => 'WB' . time() . rand(100, 999),
                'user_id' => $user_id,
                'user_membership_id' => null,
                'customer_name' => $data['full_name'],
                'customer_email' => $data['email'],
                'book_from_number' => $customer_phone,
                'customer_phone' => $data['phone'] ?? $customer_phone,
                'room_category_id' => $room->room_category_id,
                'arrival_date' => $checkInDate,
                'departure_date' => $checkOutDate,
                'adult' => $data['adults'],
                'child' => $data['children'],
                'total_rent' => 0,
                'grand_total' => 0,
                'currency_symbol' => $bs->base_currency_symbol ?? '',
                'currency_symbol_position' => $bs->base_currency_symbol_position ?? 'left',
                'currency_text' => $bs->base_currency_text ?? '',
                'currency_text_position' => $bs->base_currency_text_position ?? 'left',
                'payment_status' => 0,
                'booking_status' => 0,
                'source' => 'whatsapp_bot',
                'total_rooms' => $qty,
                'wp_id' => $wp_id,
                'custom_booking_fields' => json_encode($data['custom_fields']),
                'created_at' => Carbon::now($timezone),
                'updated_at' => Carbon::now($timezone),
            ]);

            $createdBookings[] = $booking;
        }

        if (!empty($createdBookings)) {
            MailService::sendBookingMail($createdBookings[0], 'new_booking_notification', $createdBookings);
        }

        return ['status' => 'success'];
    }


    public static function storeBookingForSingleAvailableRooms($data, $user_id, $languageId, $customer_phone, $wp_id)
    {
        $showrooms = @$data['available_room_ids'] ?? []; //get room Ids which are shown to user in conversation
        $room = DB::table('user_room_category_contents')
            ->join('user_room_categories', 'user_room_category_contents.room_id', '=', 'user_room_categories.id')
            ->where('user_room_categories.user_id', $user_id)
            ->where('user_room_category_contents.language_id', $languageId)
            ->where('user_room_categories.status', 1)
            ->where('user_room_categories.id', $showrooms[0])
            ->select(
                'user_room_categories.id as room_category_id',
                'user_room_categories.user_id',
                'user_room_category_contents.title as room_title',
                'user_room_category_contents.slug as room_slug'
            )->first();
        if (!$room) {
            return ['status' => 'room_not_found'];
        }

        //check room availability
        $title = $room->room_title;
        $qty = (int) ($data['room_quantity'] ?? 1);

        $availableCount = self::getAvailableRoomCount(
            $user_id,
            $room->room_category_id,
            $data['check_in_date'],
            $data['check_out_date']
        );
        if ($availableCount < $qty) {
            $roomName = $title ?: 'this room';
            $availableText = $availableCount > 0 ? "{$availableCount} available" : 'no availability';
            return [
                'status' => 'error',
                'message' => "Sorry, {$roomName} is not available for those dates ({$availableText}). Please choose another room."
            ];
        }

        $timezone = TimzeZoneService::getUserTimeZone($user_id);
        $checkInDate = Carbon::parse($data['check_in_date'], $timezone)->format('Y-m-d');
        $checkOutDate = Carbon::parse($data['check_out_date'], $timezone)->format('Y-m-d');

        //get base currency
        $bs = DB::table('user_basic_settings')
            ->where('user_id', $user_id)
            ->select('base_currency_symbol', 'base_currency_symbol_position', 'base_currency_text', 'base_currency_text_position')
            ->first();

        //store bookings
        $booking =  RoomBooking::create([
            'booking_number' => 'WB' . time() . rand(100, 999),
            'user_id' => $user_id,
            'user_membership_id' => null,
            'customer_name' => $data['full_name'],
            'customer_email' => $data['email'],
            'customer_phone' => $customer_phone,
            'room_category_id' => $room->room_category_id,
            'arrival_date' => $checkInDate,
            'departure_date' => $checkOutDate,
            'adult' => $data['adults'],
            'child' => $data['children'],
            'total_rent' => 0,
            'grand_total' => 0,
            'currency_symbol' => $bs->base_currency_symbol ?? '',
            'currency_symbol_position' => $bs->base_currency_symbol_position ?? 'left',
            'currency_text' => $bs->base_currency_text ?? '',
            'currency_text_position' => $bs->base_currency_text_position ?? 'left',
            'payment_status' => 0,
            'booking_status' => 0,
            'source' => 'whatsapp_bot',
            'total_rooms' => $qty,
            'wp_id' => $wp_id,
            'custom_booking_fields' => json_encode($data['custom_fields']),
            'created_at' => Carbon::now($timezone),
            'updated_at' => Carbon::now($timezone),
        ]);

        MailService::sendBookingMail($booking, 'new_booking_notification');

        return ['status' => 'success'];
    }

    /**
     * Get available room count for given room category and dates, considering overlapping bookings with
     * assigned rooms and bookings with reserved_dates_info
     */
    public static function getAvailableRoomCount($user_id, $roomCategoryId, $checkIn, $checkOut)
    {
        $totalActiveCount = DB::table('user_rooms')
            ->where('room_category_id', $roomCategoryId)
            ->where('status', 1)
            ->count();

        $bookedCount = self::getUnassignedBookedRoomCount($user_id, $roomCategoryId, $checkIn, $checkOut);
        return max(0, $totalActiveCount - $bookedCount);
    }


    /**
     * Get unassigned booked room count for given room category and dates
     */
    public static function getUnassignedBookedRoomCount($user_id, $roomCategoryId, $checkIn, $checkOut)
    {
        $timezone = TimzeZoneService::getUserTimeZone($user_id);
        $checkIn = Carbon::parse($checkIn, $timezone)->format('Y-m-d');
        $checkOut = Carbon::parse($checkOut, $timezone)->format('Y-m-d');

        return RoomBooking::where('user_id', $user_id)
            ->where('room_category_id', $roomCategoryId)
            ->where('arrival_date', '<', $checkOut)
            ->where('departure_date', '>', $checkIn)
            ->where('booking_status', '!=', 2)
            ->where(function ($query) {
                $query->whereNull('reserved_dates_info')
                    ->orWhere('reserved_dates_info', '')
                    ->orWhere('reserved_dates_info', '[]');
            })
            ->sum('total_rooms');
    }
}
