<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

trait MiscellaneousTrait
{
    public static function getCurrencyInfoUser()
    {
        $user = Auth::guard('web')->user();
        $baseCurrencyInfo = DB::table('user_basic_settings')->where('user_id', $user->id)
            ->select('base_currency_symbol', 'base_currency_symbol_position', 'base_currency_text', 'base_currency_text_position', 'base_currency_rate')
            ->first();

        return $baseCurrencyInfo;
    }
}
