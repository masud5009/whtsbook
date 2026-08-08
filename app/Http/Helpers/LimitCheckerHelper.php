<?php

namespace App\Http\Helpers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Package;
use App\Models\User\Room;
use App\Models\Membership;
use App\Models\User\Coupon;
use App\Models\AiUsageToken;
use App\Models\User\Language;
use App\Models\User\Whatsapp;
use App\Models\User\RoomNumber;
use App\Models\User\RoomBooking;
use App\Models\User\Journal\Blog;
use App\Models\User\PackageCoupon;
use App\Models\User\PackageBooking;
use App\Models\User\CustomPage\Page;
use App\Models\User\PackageCategory;
use App\Models\User\Package as UserPackage;

class LimitCheckerHelper
{

    public static function getMembershipId($userId)
    {
        return Membership::query()->where([
            ['user_id', '=', $userId],
            ['status', '=', 1],
            ['start_date', '<=', Carbon::now()->format('Y-m-d')],
            ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
        ])
            ->pluck('package_id')
            ->first();
    }

    public static function roomsLimit(int $user_id)
    {
        $id = Membership::query()->where([
            ['user_id', '=', $user_id],
            ['status', '=', 1],
            ['start_date', '<=', Carbon::now()->format('Y-m-d')],
            ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
        ])
            ->pluck('package_id')
            ->first();
        if (isset($id)) {
            $package = Package::query()->select('room_limit')->findOrFail($id);
        }
        return isset($id) && isset($package) ? $package->room_limit : 0;
    }
    public static function roomCategoriesLimit(int $user_id)
    {
        $id = Membership::query()->where([
            ['user_id', '=', $user_id],
            ['status', '=', 1],
            ['start_date', '<=', Carbon::now()->format('Y-m-d')],
            ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
        ])
            ->pluck('package_id')
            ->first();
        if (isset($id)) {
            $package = Package::query()->select('room_categories_limit')->findOrFail($id);
        }
        return isset($id) && isset($package) ? $package->room_categories_limit : 0;
    }
    public static function roomBookingsLimit(int $user_id)
    {
        $id = Membership::query()->where([
            ['user_id', '=', $user_id],
            ['status', '=', 1],
            ['start_date', '<=', Carbon::now()->format('Y-m-d')],
            ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
        ])
            ->pluck('package_id')
            ->first();
        if (isset($id)) {
            $package = Package::query()->select('room_booking_limit')->findOrFail($id);
        }
        return isset($id) && isset($package) ? $package->room_booking_limit : 0;
    }




    public static function languagesLimit(int $user_id)
    {
        $id = Membership::query()->where([
            ['user_id', '=', $user_id],
            ['status', '=', 1],
            ['start_date', '<=', Carbon::now()->format('Y-m-d')],
            ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
        ])
            ->pluck('package_id')
            ->first();
        if (isset($id)) {
            $language = Package::query()->select('language_limit')->findOrFail($id);
        }
        return isset($id) && isset($language) ? $language->language_limit : 0;
    }


    // Room Count Section

    public static function countRooms(int $userId)
    {
        $countRooms =  RoomNumber::where('user_id', $userId);
        return $countRooms ? $countRooms->count() : 0;
    }
    public static function countRoomCategorys(int $userId)
    {
        $countRooms =  Room::where('user_id', $userId);
        return $countRooms ? $countRooms->count() : 0;
    }

    public static function countRoomBookings($userId)
    {
        $user = User::with(['memberships' => function ($q) {
            $q->where('status', 1)
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('expire_date', '>=', Carbon::today());
        }])->where('id', $userId)->first();

        if (!is_null($user) && $user->memberships->isNotEmpty()) {
            $membership_id =  $user->memberships->first()->id;
        }
        if (isset($membership_id) && !empty($membership_id)) {
            $countRoomBooking =  RoomBooking::where('user_id', $userId)->where('user_membership_id', $membership_id);
            return $countRoomBooking ? $countRoomBooking->count() : 0;
        }
        return  0;
    }

    // Package Count Section
    public static function roomBookingCountUser($userId)
    {
        $roomBookingsUser = RoomBooking::whereHas('hotelRoom')
            ->where('user_id', $userId)
            ->select('room_id');
        return $roomBookingsUser ? $roomBookingsUser->count() : 0;
    }

    public static function countLanguages($userId)
    {
        $countLanguages = Language::where('user_id', $userId)->where('added_type', 'user');
        return $countLanguages ? $countLanguages->count() : 0;
    }

    public static function availableToken($userId)
    {
        $aiUsage = AiUsageToken::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$aiUsage) {
            return 0;
        }

        return (int) ($aiUsage->total_usable_tokens ?? 0)
            + (int) ($aiUsage->extend_token ?? 0)
            - (int) ($aiUsage->total_tokens ?? 0);
    }

    public static function userFeaturesCount($userId)
    {
        $user = User::find($userId);

        $userFeaturesCount = [];
        $userFeaturesCount['rooms'] = RoomNumber::where('user_id', $userId)->count();
        $userFeaturesCount['room_categories'] = Room::where('user_id', $userId)->count();
        $userFeaturesCount['room_bookings'] = self::countRoomBookings($user->id);
        $userFeaturesCount['languages'] = self::countLanguages($user->id);
        $userFeaturesCount['wp_numbers'] =  Whatsapp::where('user_id', $user->id)->count();

        return $userFeaturesCount;
    }
}
