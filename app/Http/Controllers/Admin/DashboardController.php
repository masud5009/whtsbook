<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Language;
use App\Models\Membership;
use App\Models\AiUsageToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['incomes'] = Membership::select(DB::raw('MONTH(created_at) month'), DB::raw('sum(price) total'))->where('status', 1)->groupBy('month')->whereYear('created_at', date('Y'))->get();
        $data['users'] = User::join('memberships', 'users.id', '=', 'memberships.user_id')
            ->select(DB::raw('MONTH(users.created_at) month'), DB::raw('count(*) total'))
            ->groupBy('month')
            ->whereYear('users.created_at', date('Y'))
            ->where([
                ['memberships.status', '=', 1],
                ['memberships.start_date', '<=', Carbon::now()->format('Y-m-d')],
                ['memberships.expire_date', '>=', Carbon::now()->format('Y-m-d')]
            ])
            ->get();
        $data['defaultLang'] = Language::where('is_default', 1)->first();

        $data['aiUsage'] = AiUsageToken::select(
            DB::raw('SUM(total_tokens) as total_usage'),
            DB::raw('SUM(total_usable_tokens) as total_usable_tokens'),
            DB::raw('SUM(extend_token) as total_extend_tokens')
        )->first();

        return view('admin.dashboard', $data);
    }

    public function changeTheme(Request $request)
    {
        return redirect()->back()->withCookie(cookie()->forever('admin-theme', $request->theme));
    }
}
