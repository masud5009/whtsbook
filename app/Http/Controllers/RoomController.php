<?php

namespace App\Http\Controllers;

use App\Models\User\Language;
use App\Models\User\RoomAmenity;
use App\Models\User\RoomContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    public function roomDetails($userId, $slug)
    {
        $language = $this->defaultLang($userId);

        $roomId = DB::table('user_room_category_contents')
            ->where('slug', $slug)
            ->where('user_id', $userId)
            ->value('room_id');


        $details = RoomContent::leftJoin('user_room_categories', 'user_room_categories.id', '=', 'user_room_category_contents.room_id')
            ->where('user_room_category_contents.language_id', $language->id)
            ->where('user_room_categories.status', 1)
            ->where('user_room_category_contents.room_id', $roomId)
            ->first();

        if ($details->room_details_page == 0) {
            \abort(404);
        }


        $queryResult['details'] = $details;
        $amms = [];
        if (!empty($details->amenities_index) && $details->amenitiesamenities_index != '[]') {
            $ammIds = json_decode($details->amenities_index, true);

            $ammenities = RoomAmenity::whereIn('indx', $ammIds)->where('user_id', $userId)->where('language_id', $language->id)
                ->where('status', 1)->orderBy('serial_number', 'ASC')->get();
            foreach ($ammenities as $key => $ammenity) {
                $amms[] = $ammenity->name;
            }
        }

        $queryResult['amms'] = $amms;

        $queryResult['userBs'] = DB::table('user_basic_settings')
            ->where('user_id', $userId)
            ->select('favicon', 'primary_color', 'secondary_color', 'website_title')
            ->first();

        $queryResult['defaultLang'] = $language;

        return view('user-front.room-details', $queryResult);
    }


    /**
     * get language
     */
        private function defaultLang($user_id)
    {
        $code = session()->get('user_lang_' . $user_id);

        if (!is_null($code)) {
            $defaultLang = Language::where('user_id', $user_id)->where('code', $code)->first();
        }


            $defaultLang = Language::where('user_id', $user_id)->where('is_default', 1)->first();


        return $defaultLang;
    }
}
