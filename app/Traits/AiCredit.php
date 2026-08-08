<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait AiCredit
{
    public function admin_remaining_credit()
    {
        $usage = DB::table('ai_useages')
            ->selectRaw('
                COALESCE(SUM(total_tokens), 0) as total_used,
                COALESCE(SUM(total_usable_tokens), 0) as total_usable
            ')
            ->first();

        $adminTotal = (int) DB::table('basic_settings')
            ->value('admin_total_ai_token');

        // remaining = total - used
        $remaining = $adminTotal - $usage->total_used;

        return max(0, $remaining);
    }
}
