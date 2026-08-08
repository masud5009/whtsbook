<?php

namespace App\Services;

use Carbon\Carbon;
use App\Services\Common;
use App\Models\User\Room;
use App\Services\AiService;
use App\Models\User\Language;
use App\Models\User\RoomAmenity;
use App\Models\User\RoomBooking;
use App\Models\User\RoomContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoomDetailsService
{
    protected $aiService;

    public function __construct()
    {
        $this->aiService = new AiService();
    }

    /**
     * Get available rooms
     */
    public function getAvailableRooms($data, $user_id, $customerPhone)
    {
        $check_in = $data['check_in_date'] ?? null;
        $check_out = $data['check_out_date'] ?? null;
        $adults = $data['adults'] ?? 0;
        $children = $data['children'] ?? 0;

        if (empty($check_in) || empty($check_out)) {
            return "Please provide both check-in and check-out dates.";
        }

        try {
            $checkInDate = Carbon::parse($check_in)->toDateString();
            $checkOutDate = Carbon::parse($check_out)->toDateString();
        } catch (\Exception $e) {
            return "Please provide valid check-in and check-out dates (YYYY-MM-DD).";
        }

        if ($checkInDate >= $checkOutDate) {
            return "Check-in date must be before check-out date.";
        }

        $personCount = (int)$adults + (int)$children;
        $language = $this->getUserDefaultLanguage($user_id);
        if (!$language) {
            return "Hotel language settings are missing. Please contact the hotel.";
        }

        $rooms = $this->findRooms($language, $user_id, $check_in, $check_out);

        $perfectMatch = [];
        $biggerCandidates = [];

        foreach ($rooms as $room) {
            $totalCapacity = (int)$room->adult + (int)($room->child ?? 0);

            if ($totalCapacity === (int)$personCount) {
                $perfectMatch[] = $room;
            } elseif ($totalCapacity > (int)$personCount) {
                $extra = $totalCapacity - (int)$personCount;
                $biggerCandidates[] = ['room' => $room, 'extra' => $extra];
            }
        }

        if (!empty($perfectMatch)) {
            $suitableRooms = collect($perfectMatch);
        } else {
            // closest bigger only (min extra)
            $suitableRooms = collect($biggerCandidates)
                ->sortBy('extra')
                ->values();

            if ($suitableRooms->isNotEmpty()) {
                $minExtra = $suitableRooms->first()['extra'];
                $suitableRooms = $suitableRooms
                    ->where('extra', $minExtra)
                    ->pluck('room')
                    ->values();
            } else {
                $suitableRooms = collect(); // nothing fits
            }
        }

        $cacheKey = 'context_slots_' . $customerPhone . '_' . $user_id;
        if ($suitableRooms->isNotEmpty()) {
            $context = Cache::get($cacheKey, []);

            // Replace old IDs with new ones
            $context['available_room_ids'] = $suitableRooms->pluck('id')->toArray();
            $context['available_room_titles'] = $suitableRooms->pluck('title')->toArray();

            // Re‑store with the original 12‑hour expiration
            Cache::put($cacheKey, $context, now()->addDays(30));

            return $this->formatedRooms($suitableRooms, $user_id);
        }

        // Try room combinations with AI Intelligence
        if ($suitableRooms->isEmpty() && $rooms->isNotEmpty()) {
            $availablePool = $rooms->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->title,
                'cap' => ($r->adult + ($r->child ?? 0)),
                'total' => Common::priceCalculation($r, $check_in, $check_out)['totalPrice'],
                'daily' => $r->regular_price
            ])->toArray();

            $bs = DB::table('user_basic_settings')
                ->where('user_id', $user_id)
                ->select('base_currency_symbol as sym', 'base_currency_symbol_position as pos')
                ->first();

            $sym = $bs->sym ?? '';
            $pos = $bs->pos ?? 'left';

            $aiPrompt = "Context: Need stay for {$personCount} pax from {$check_in} to {$check_out}.
Rooms: " . json_encode($availablePool) . "
Goal: Select best combo for {$personCount} pax.
Rules:
-Min rooms, max cost-efficiency.
-Format: Group same rooms (e.g., '*2 x Room Name*'). Show Room Name, Qty, Cap/room, Group Total.
-Total Price: Show combined total clearly at end.
-Style: Friendly, respond in customer's language.
-Formatting: Use *Text* for bold. NO double stars (**), NO spaces after stars.
IMPORTANT (HIDDEN DATA): At the very end of your response, strictly provide the selected room IDs in this format: [IDS: id1, id2, id3]. Do not explain this part.
Currency: {$sym} ({$pos})
-Reply in customer's language.";

            $replyText = $this->aiService->getAiResponse($aiPrompt, $user_id);

            if ($replyText) {
                preg_match('/\[IDS:\s*([\d\s,]+)\]/', $replyText, $matches);

                $selectedIds = [];
                if (!empty($matches[1])) {
                    $selectedIds = array_map('trim', explode(',', $matches[1]));
                    $replyText = preg_replace('/\[IDS:\s*([\d\s,]+)\]/', '', $replyText);
                }

                $context = Cache::get($cacheKey, []);
                $context['available_room_ids'] = $selectedIds;
                $context['available_room_titles'] = RoomContent::whereIn('id', $selectedIds)
                    ->where('language_id', $language->id)
                    ->pluck('title')->toArray();
                Cache::put($cacheKey, $context, now()->addDays(30));

                return trim($replyText);
            }
        }

        if ($rooms->isEmpty())  return "Sorry, no rooms are available for the selected dates.";
        $max = $rooms->max(fn($r) => $r->adult + ($r->child ?? 0));
        return "Sorry, no room fits {$personCount} people. Our largest available room holds {$max} guests.";
    }

    /**
     * Get available rooms by capacity
     */
    private function findRooms($language, $user_id, $checkIn = null, $checkOut = null)
    {
        $roomIds = [];
        if ($checkIn && $checkOut) {
            $checkInDate  = Carbon::parse($checkIn)->toDateString();
            $checkOutDate = Carbon::parse($checkOut)->toDateString();
            if ($checkInDate >= $checkOutDate) return collect();

            $rooms = Room::with(['numbers' => function ($q) {
                $q->where('status', 1)
                    ->select('id', 'room_category_id');
            }])
                ->select('id')
                ->where('user_id', $user_id)
                ->get();

            $overlapBookings = RoomBooking::query()
                ->where('user_id', $user_id)
                ->where('arrival_date', '<', $checkOut)
                ->where('departure_date', '>', $checkIn)
                ->where('booking_status', '!=', 2)
                ->get(['id', 'room_number_id', 'reserved_dates_info']);

            $blocked = [];
            foreach ($overlapBookings as $b) {
                if (!empty($b->room_number_id)) {
                    $blocked[(int)$b->room_number_id] = true;
                }

                if (!empty($b->reserved_dates_info)) {
                    $items = $b->reserved_dates_info;
                    if (is_string($items)) {
                        $decoded = json_decode($items, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $items = $decoded;
                        }
                    }
                    if (is_array($items)) {
                        foreach ($items as $it) {
                            if (!isset($it['room_id'], $it['date'])) {
                                continue;
                            }
                            $d = $it['date'];
                            if ($d >= $checkIn && $d < $checkOut) {
                                $blocked[(int)$it['room_id']] = true;
                            }
                        }
                    }
                }
            }

            foreach ($rooms as $room) {
                $activeIds = $room->numbers->pluck('id')->all();
                if (empty($activeIds)) {
                    continue;
                }

                $allBlocked = true;
                foreach ($activeIds as $rid) {
                    if (!isset($blocked[$rid])) {
                        $allBlocked = false;
                        break;
                    }
                }

                if ($allBlocked) {
                    $roomIds[] = $room->id;
                }
            }
            $roomIds = array_values(array_unique($roomIds));
        }

        return DB::table('user_room_category_contents')
            ->join('user_room_categories', function ($join) use ($user_id) {
                $join->on('user_room_category_contents.room_id', '=', 'user_room_categories.id')
                    ->where('user_room_categories.user_id', $user_id);
            })
            ->where('user_room_category_contents.language_id', $language->id)
            ->where('user_room_categories.status', 1)
            ->when(!empty($roomIds), fn($q) => $q->whereNotIn('user_room_categories.id', $roomIds))
            ->select(
                'user_room_categories.id',
                'user_room_categories.adult',
                'user_room_categories.child',
                'user_room_categories.bed',
                'user_room_categories.bath',
                'user_room_categories.regular_price',
                'user_room_categories.weekend_price',
                'user_room_categories.seasonal_price',
                'user_room_categories.seasonal_weekend_price',
                'user_room_categories.weekend',
                'user_room_categories.seasonal_dates',
                'user_room_categories.seasonal_weekend',
                'user_room_categories.amenities_index',
                'user_room_categories.room_details_page',
                'user_room_categories.details_link',
                'user_room_category_contents.title',
                'user_room_category_contents.slug',
            )
            ->get();
    }

    /**
     * Get Rooms
     */
    private function formatedRooms($rooms, $user_id)
    {
        if (empty($rooms) || count($rooms) === 0) {
            return "😔 Sorry! No rooms available for your dates.\n"
                . "👉 Try again with a new date.";
        }

        $bs = DB::table('user_basic_settings')
            ->where('user_id', $user_id)
            ->select('base_currency_symbol', 'base_currency_symbol_position')
            ->first();

        $symbol   = $bs->base_currency_symbol ?? '';
        $position = $bs->base_currency_symbol_position ?? 'left';

        $separator = str_repeat('━', 22);
        $spacer = str_repeat(' ', 7);

        $replyText  = "✅ *Available Rooms*\n";
        $replyText .= "_You can choose from the following rooms:_\n";
        $replyText .= $separator;

        foreach ($rooms as $room) {
            $adult = (int) ($room->adult ?? 0);
            $child = (int) ($room->child ?? 0);
            $total = $adult + $child;

            $adultText = $adult . ' Adult' . ($adult > 1 ? 's' : '');
            $childText = $child > 0 ? (' + ' . $child . ' Child' . ($child > 1 ? 'ren' : '')) : '';

            if (!empty($room->details_link) && (int) $room->room_details_page === 0) {
                $url = $room->details_link;
            } else {
                $url = route('front.room.details', [$user_id, $room->slug]);
            }

            $replyText .= "\n\n🏨 *Room Name:* {$room->title}\n";
            $replyText .= "👥 {$adultText}{$childText} (Total: {$total})\n";
            $replyText .= "🛏️ *Bed:* {$room->bed}   🚿 *Bath:* {$room->bath}\n";

            $replyText .= "\n💰 *Room Pricing (Per Night):*\n\n";

            // Regular
            $replyText .= "🔹 *Regular:* " . currencySymbolPrice($room->regular_price, $symbol, $position) . "\n";
            $replyText .= $spacer . "_Note: Normal day price_\n";

            // Weekend
            if (!empty($room->weekend_price) && $room->weekend_price > 0) {
                $weekendDays = !empty($room->weekend) ? " ({$room->weekend})" : '';
                $replyText .= "\n🔹 *Weekend:* " . currencySymbolPrice($room->weekend_price, $symbol, $position) . $weekendDays . "\n";
                $replyText .= $spacer . "_Note: Weekend day price_\n";
            }

            // Seasonal
            if (!empty($room->seasonal_price) && $room->seasonal_price > 0) {
                $replyText .= "\n🔹 *Seasonal:* " . currencySymbolPrice($room->seasonal_price, $symbol, $position) . "\n";

                if (!empty($room->seasonal_dates)) {
                    $dates = json_decode($room->seasonal_dates, true);
                    if (!empty($dates)) {
                        $replyText .= $spacer . "*Seasonal Periods:*\n";
                        foreach ($dates as $d) {
                            $replyText .= $spacer . "• {$d['start']} → {$d['end']}\n";
                        }
                        $replyText .= "_Note: Dates when seasonal price applies_\n";
                    }
                }

                if (
                    !empty($room->seasonal_weekend) &&
                    !empty($room->seasonal_weekend_price) &&
                    $room->seasonal_weekend_price > 0
                ) {
                    $swDays = $room->seasonal_weekend;
                    $replyText .= "\n🔹 *Seasonal Weekend:*\n";
                    $replyText .= "*Price:* " . currencySymbolPrice($room->seasonal_weekend_price, $symbol, $position) . " ({$swDays}).\n";
                    $replyText .= "_Note: Weekend price during seasonal dates_\n";
                }
            }

            $replyText .= "\n🔗 *Details & Photos:* {$url}\n";
            $replyText .= $separator;
        }
        return $replyText;
    }

    /**
     * Get room image link by shown room name only
     */
    public function roomImageLink($roomName, $customer_phone, $user_id)
    {
        $language = $this->getUserDefaultLanguage($user_id);
        if (!$language) {
            return null;
        }

        // get shown rooms from cache to validate if user is asking for details of shown rooms or not
        $cacheKey = 'context_slots_' . $customer_phone . '_' . $user_id;
        $cacheData = Cache::get($cacheKey, []);
        $cacheContext = $cacheData['slots'] ?? [];
        $showrooms = $cacheContext['available_room_ids'] ?? [];
        $roomName = trim((string)$roomName);

        if (empty($showrooms)) {
            return "Please tell me your check-in and check-out dates first so I can show the available rooms. After that, you can ask for details about any room.";
        }

        if ($roomName === '') {
            return null;
        }

        // get only shown room titles
        $showroomsTitles = DB::table('user_room_category_contents')
            ->whereIn('room_id', $showrooms)
            ->where('language_id', $language->id)
            ->pluck('title')
            ->toArray();

        // match only against shown rooms
        $matchedTitle = collect($showroomsTitles)->first(function ($dbTitle) use ($roomName) {
            return stripos($dbTitle, $roomName) !== false ||
                stripos($roomName, $dbTitle) !== false;
        });

        if (!$matchedTitle) {
            return "Please choose a room from the options shown.";
        }

        $room = DB::table('user_room_category_contents')
            ->join('user_room_categories', 'user_room_category_contents.room_id', '=', 'user_room_categories.id')
            ->where('user_room_categories.user_id', $user_id)
            ->where('user_room_category_contents.language_id', $language->id)
            ->where('user_room_categories.status', 1)
            ->whereIn('user_room_category_contents.room_id', $showrooms)
            ->where('user_room_category_contents.title', $matchedTitle)
            ->select(
                'user_room_category_contents.slug',
                'user_room_categories.room_details_page',
                'user_room_categories.details_link'
            )
            ->first();

        if (!$room) {
            return "Please choose a room from the options shown.";
        }

        if (!is_null($room->details_link) && $room->room_details_page == 0) {
            return $room->details_link;
        }

        if (!is_null($room->slug)) {
            return route('front.room.details', [$user_id, $room->slug]);
        }

        return "Please choose a room from the options shown.";
    }

    /**
     * Get room details
     */
    public function getSpecificRooms($roomName, $customer_phone, $user_id)
    {
        $language = $this->getUserDefaultLanguage($user_id);

        // get shown rooms from cache to validate if user is asking for details of shown rooms or not
        $cacheKey = 'context_slots_' . $customer_phone . '_' . $user_id;
        $cached = Cache::get($cacheKey, []);
        $cacheContext = $cached['slots'] ?? [];
        $showrooms = $cacheContext['available_room_ids'] ?? [];
        $roomName = trim((string) $roomName);

        if ($roomName === '' || empty($showrooms)) {
            return 'room_not_found';
        }

        $showroomsTitles = DB::table('user_room_category_contents')
            ->whereIn('room_id', $showrooms)
            ->where('language_id', $language->id)
            ->pluck('title')
            ->toArray();

        // match only against shown rooms
        $matchedTitle = collect($showroomsTitles)->first(function ($dbTitle) use ($roomName) {
            if ($roomName === '') {
                return false;
            }

            return stripos($dbTitle, $roomName) !== false ||
                stripos($roomName, $dbTitle) !== false;
        });

        // if no match found against shown rooms, then return with error instead of fetching details of any other room
        if (!$matchedTitle) {
            return 'room_not_found';
        }

        // fetch the room details of matched title for shown rooms only
        $room = DB::table('user_room_category_contents')
            ->join('user_room_categories', 'user_room_category_contents.room_id', '=', 'user_room_categories.id')
            ->where('user_room_categories.user_id', $user_id)
            ->where('user_room_category_contents.language_id', $language->id)
            ->where('user_room_categories.status', 1)
            ->where('user_room_category_contents.title', $matchedTitle)
            ->select(
                'user_room_categories.id',
                'user_room_categories.adult',
                'user_room_categories.child',
                'user_room_categories.bed',
                'user_room_categories.bath',
                'user_room_categories.regular_price',
                'user_room_categories.weekend_price',
                'user_room_categories.seasonal_price',
                'user_room_categories.seasonal_weekend_price',
                'user_room_categories.weekend',
                'user_room_categories.seasonal_dates',
                'user_room_categories.seasonal_weekend',
                'user_room_categories.amenities_index',
                'user_room_categories.room_details_page',
                'user_room_categories.details_link',
                'user_room_category_contents.title',
                'user_room_category_contents.slug',
            )
            ->first();

        if (!$room) {
            return 'room_not_found';
        }

        $bs = DB::table('user_basic_settings')
            ->where('user_id', $user_id)
            ->select('base_currency_symbol', 'base_currency_symbol_position')
            ->first();

        $symbol   = $bs?->base_currency_symbol ?? '';
        $position = $bs?->base_currency_symbol_position ?? 'left';
        $separator = str_repeat('━', 22);
        $spacer = str_repeat(' ', 7);

        $adult = (int) ($room->adult ?? 0);
        $child = (int) ($room->child ?? 0);
        $totalCapacity = $adult + $child;

        $adultText = $adult . ' Adult' . ($adult > 1 ? 's' : '');
        $childText = $child > 0 ? (' + ' . $child . ' Child' . ($child > 1 ? 'ren' : '')) : '';

        if (!empty($room->details_link) && (int) $room->room_details_page === 0) {
            $url = $room->details_link;
        } else {
            $url = route('front.room.details', [$user_id, $room->slug]);
        }

        $replyText  = "🏨 *{$room->title}*\n";
        $replyText .= $separator . "\n";

        $replyText .= "👥 {$adultText}{$childText} (Total: {$totalCapacity})\n";
        $replyText .= "🛏️ *Bed:* {$room->bed}   🚿 *Bath:* {$room->bath}\n";

        $replyText .= "\n💰 *Room Pricing (Per Night):*\n\n";

        // Regular
        $replyText .= "🔹 *Regular:* " . currencySymbolPrice($room->regular_price, $symbol, $position) . "\n";
        $replyText .= $spacer . "_Note: Normal day price_\n";

        // Weekend
        if (!empty($room->weekend_price) && $room->weekend_price > 0) {
            $weekendDays = !empty($room->weekend) ? " ({$room->weekend})" : '';
            $replyText .= "\n🔹 *Weekend:* " . currencySymbolPrice($room->weekend_price, $symbol, $position) . $weekendDays . "\n";
            $replyText .= $spacer . "_Note: Weekend day price_\n";
        }

        // Seasonal
        if (!empty($room->seasonal_price) && $room->seasonal_price > 0) {
            $replyText .= "\n🔹 *Seasonal:* " . currencySymbolPrice($room->seasonal_price, $symbol, $position) . "\n";

            // Seasonal periods
            if (!empty($room->seasonal_dates)) {
                $dates = json_decode($room->seasonal_dates, true);
                if (!empty($dates)) {
                    $replyText .= $spacer . "*Seasonal Periods:*\n";
                    foreach ($dates as $d) {
                        $replyText .= $spacer . "• {$d['start']} → {$d['end']}\n";
                    }
                    $replyText .= "_Note: Dates when seasonal price applies_\n";
                }
            }

            // Seasonal weekend
            if (
                !empty($room->seasonal_weekend) &&
                !empty($room->seasonal_weekend_price) &&
                (float) $room->seasonal_weekend_price > 0
            ) {
                $swDays = $room->seasonal_weekend;
                $replyText .= "\n🔹 *Seasonal Weekend:*\n";
                $replyText .= "*Price:* " . currencySymbolPrice($room->seasonal_weekend_price, $symbol, $position) . " ({$swDays}).\n";
                $replyText .= "_Note: Weekend price during seasonal dates_\n";
            }
        }

        // Amenities
        if (!empty($room->amenities_index)) {
            $amenities = $this->getAmenities($room, $user_id);
            if (!empty($amenities)) {
                $replyText .= "\n✨ *Amenities & Features:*\n";
                foreach ($amenities as $amenity) {
                    $replyText .= "• {$amenity}\n";
                }
            }
        }

        $replyText .= "\n🔗 *Details & Photos:* {$url}";
        return $replyText;
    }
    /**
     * Get room amenities
     */
    private function getAmenities($hotel, $user_id)
    {
        $language = $this->getUserDefaultLanguage($user_id);
        if (!$language) return [];


        $index = $hotel->amenities_index ?? null;
        if (!$index) return [];

        $ammIds = json_decode($index, true);
        if (!is_array($ammIds)) return [];

        $ammIds = array_values(array_unique(array_filter($ammIds, fn($v) => $v !== null && $v !== '')));
        if (empty($ammIds)) return [];

        return RoomAmenity::query()
            ->where('user_id', $user_id)
            ->where('language_id', $language->id)
            ->where('status', 1)
            ->whereIn('indx', $ammIds)
            ->orderBy('serial_number')
            ->pluck('name')
            ->all();
    }

    /**
     * Get user default language
     */
    private function getUserDefaultLanguage($user_id)
    {
        return Language::where('user_id', $user_id)->where('is_default', 1)->first();
    }
}
