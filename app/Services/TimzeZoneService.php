<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TimzeZoneService
{
    public static function getAdminTimeZone()
    {
        return config('app.timezone'); //data example America/Maceio
    }

    public static function getUserTimeZone($user_id)
    {
        $userSetting = DB::table('user_basic_settings')->where('user_id', $user_id)->value('timezone');
        return $userSetting ?? self::getAdminTimeZone(); //data example America/Maceio
    }
}
