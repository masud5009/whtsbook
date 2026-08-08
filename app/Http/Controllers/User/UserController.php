<?php

namespace App\Http\Controllers\User;

use App;
use Carbon\Carbon;
use App\Models\Package;
use App\Models\User\Room;
use App\Models\Membership;
use App\Constants\Constant;
use App\Models\AiUsageToken;
use App\Models\BasicSetting;
use Illuminate\Http\Request;
use App\Http\Helpers\Uploader;
use App\Http\Helpers\LimitCheckerHelper;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use App\Models\User\RoomBooking;
use App\Models\User\Staff;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Services\Payment\PaymentHandler;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('setUserLang');
    }
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $data['user'] = $user;
        $currentYear = (int) Carbon::now()->year;
        $createdYear = $user->created_at ? (int) Carbon::parse($user->created_at)->year : $currentYear;
        $minimumYear = min($createdYear, $currentYear);

        $data['dashboardYears'] = array_reverse(range($minimumYear, $currentYear));
        $data['selectedBookingYear'] = $this->sanitizeDashboardYear(
            $request->input('booking_year'),
            $minimumYear,
            $currentYear
        );
        $data['selectedIncomeYear'] = $this->sanitizeDashboardYear(
            $request->input('income_year'),
            $minimumYear,
            $currentYear
        );

        $data['roomsCount'] = Room::where('user_id', $user->id)->count();
        $bookingQuery = RoomBooking::where('user_id', $user->id);

        $data['allRbCount'] = (clone $bookingQuery)->count();
        $data['pendingBooking'] = (clone $bookingQuery)->where('booking_status', 0)->count();
        $data['rejectedBooking'] = (clone $bookingQuery)->where('booking_status', 2)->count();

        $bookingSourceCounts = (clone $bookingQuery)
            ->selectRaw("
                CASE
                    WHEN source = 'whatsapp_bot' THEN 'whatsapp_bot'
                    ELSE 'web'
                END as booking_source,
                COUNT(*) as total
            ")
            ->groupBy('booking_source')
            ->pluck('total', 'booking_source');

        $data['webBookingSourceCount'] = (int) ($bookingSourceCounts['web'] ?? 0);
        $data['whatsappBotBookingSourceCount'] = (int) ($bookingSourceCounts['whatsapp_bot'] ?? 0);
        $data['webBookingSourcePercentage'] = $data['allRbCount'] > 0
            ? round(($data['webBookingSourceCount'] / $data['allRbCount']) * 100, 1)
            : 0;
        $data['whatsappBotBookingSourcePercentage'] = $data['allRbCount'] > 0
            ? round(($data['whatsappBotBookingSourceCount'] / $data['allRbCount']) * 100, 1)
            : 0;
        $data['memberships'] = Membership::query()->where('user_id', Auth::user()->id)
            ->orderBy('id', 'DESC')
            ->limit(10)->get();

        $data['users'] = [];

        $nextPackageCount = Membership::query()->where([
            ['user_id', Auth::guard('web')->user()->id],
            ['expire_date', '>=', Carbon::now()->toDateString()],
        ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->count();
        //current package
        $data['current_membership'] = Membership::query()->where([
            ['user_id', Auth::guard('web')->user()->id],
            ['start_date', '<=', Carbon::now()->toDateString()],
            ['expire_date', '>=', Carbon::now()->toDateString()],
        ])->where('status', 1)->whereYear('start_date', '<>', '9999')->first();
        if ($data['current_membership']) {
            $countCurrMem = Membership::query()->where([
                ['user_id', Auth::guard('web')->user()->id],
                ['start_date', '<=', Carbon::now()->toDateString()],
                ['expire_date', '>=', Carbon::now()->toDateString()],
            ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999')->count();
            if ($countCurrMem > 1) {
                $data['next_membership'] = Membership::query()->where([
                    ['user_id', Auth::guard('web')->user()->id],
                    ['start_date', '<=', Carbon::now()->toDateString()],
                    ['expire_date', '>=', Carbon::now()->toDateString()],
                ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999')->orderBy('id', 'DESC')->first();
            } else {
                $data['next_membership'] = Membership::query()->where([
                    ['user_id', Auth::guard('web')->user()->id],
                    ['start_date', '>', $data['current_membership']->expire_date],
                ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->first();
            }
            $data['next_package'] = $data['next_membership'] ? Package::query()->where('id', $data['next_membership']->package_id)->first() : null;
        }
        $data['current_package'] = $data['current_membership'] ? Package::query()->where('id', $data['current_membership']->package_id)->first() : null;
        $data['package_count'] = $nextPackageCount;

        //if row not exist then create new row for Ai Usages Tokens
        $row = AiUsageToken::where('user_id', $user->id)->lockForUpdate()->first();
        if (!$row) {
            AiUsageToken::create([
                'user_id' => $user->id,
                'total_tokens' => 0,
                'total_usable_tokens' => $data['current_package']->total_ai_token,
                'extend_token' => 0,
                'token_debt' => 0,
            ]);
        }

        $allIncomeRows = RoomBooking::where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COALESCE(SUM(paid_amount),0) as total, COALESCE(SUM(due),0) as due')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        $allBookingStatusRows = RoomBooking::where('user_id', $user->id)
            ->selectRaw("
        YEAR(created_at) as year,
        MONTH(created_at) as month,
        SUM(CASE WHEN booking_status = '1' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN booking_status = '0' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN booking_status = '2' THEN 1 ELSE 0 END) as cancelled
    ")
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        $data['allMonthlyRoomBookingIncomes'] = $allIncomeRows;
        $data['allMonthlyRoomBookingsStatus'] = $allBookingStatusRows;

        $data['monthly_room_booking_incomes'] = $allIncomeRows
            ->where('year', $data['selectedIncomeYear'])
            ->values();

        $data['monthly_room_bookings_status'] = $allBookingStatusRows
            ->where('year', $data['selectedBookingYear'])
            ->values();

        //AI token usage
        $aiUsage = AiUsageToken::where('user_id', $user->id)->first();
        $data['availableToken'] = LimitCheckerHelper::availableToken($user->id);
        $data['usedTokens'] = $aiUsage->total_tokens ?? 0;

        //payment gateways
        $data['onlineGateways'] = PaymentGateway::where('status', 1)->get();
        $data['offlineGateways'] = OfflineGateway::where('status', 1)
            ->orderBy('serial_number')
            ->get();

        $stripeInfo = PaymentGateway::where('keyword', 'stripe')->value('information');
        $stripeInfo = $stripeInfo ? json_decode($stripeInfo, true) : null;
        $data['stripe_key'] = $stripeInfo['key'] ?? null;

        $authorizeInfo = PaymentGateway::where('keyword', 'authorize.net')->value('information');
        $authorizeInfo = $authorizeInfo ? json_decode($authorizeInfo, true) : null;

        if ($authorizeInfo) {
            $data['anetSrc'] = $authorizeInfo['sandbox_check'] == 1
                ? 'https://jstest.authorize.net/v1/Accept.js'
                : 'https://js.authorize.net/v1/Accept.js';

            $data['authorizeClientKey'] = $authorizeInfo['public_key'] ?? null;
            $data['authorizeLoginId']   = $authorizeInfo['login_id'] ?? null;
        }

        $pricing = BasicSetting::getAiPricing();
        $data['price_per_token'] = $pricing['current'];
        $data['current_ai_provider'] = $pricing['provider'];

        $data['adminCurrency'] = PaymentHandler::getCurrencySettings(1, $user->id)->base_currency_text;

        return view('user.dashboard', $data);
    }

    private function sanitizeDashboardYear($year, int $minimumYear, int $maximumYear): int
    {
        $selectedYear = is_numeric($year) ? (int) $year : $maximumYear;

        if ($selectedYear < $minimumYear || $selectedYear > $maximumYear) {
            return $maximumYear;
        }

        return $selectedYear;
    }

    public function status(Request $request)
    {
        $user = Auth::user();
        $user->online_status = $request->value;
        $user->save();
        $msg = '';
        if ($request->value == 1) {
            $msg = "Profile has been made visible";
        } else {
            $msg = "Profile has been hidden";
        }
        Session::flash('success', $msg);
        return "success";
    }

    public function profile()
    {
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            return view('user.staff.profile', compact('staff'));
        }

        $user = Auth::user();
        return view('user.edit-profile', compact('user'));
    }

    public function profileupdate(Request $request)
    {
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();

            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:staff,username,' . $staff->id,
                'email' => 'required|email|max:255|unique:staff,email,' . $staff->id,
            ]);

            $staff->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
            ]);

            Session::flash('success', __('Updated Successfully'));
            return 'success';
        }

        $request->validate([
            'photo' =>  Auth::user()->photo ? 'nullable' : 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'company_name' => 'required',
            'username' => 'required|unique:users,username,' . Auth::user()->id,
            'phone' => 'required',
            'country' => 'required',
        ]);

        $input = $request->except('address');
        $input['address'] =  Purifier::clean($request->address, 'youtube');
        $data = Auth::user();
        if ($request->hasFile('photo')) {
            $profile = $request->file('photo');
            $input['photo'] = Uploader::profile_image(public_path(Constant::WEBSITE_TENANT_IMAGE), $profile,  $data->photo);
        }
        $data->update($input);

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function resetform()
    {
        return view('user.reset');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required',
            'confirmation_password' => 'required',
        ]);
        $user = Auth::user();
        if ($request->current_password) {
            if (Hash::check($request->current_password, $user->password)) {
                if ($request->new_password == $request->confirmation_password) {
                    $input['password'] = Hash::make($request->new_password);
                } else {
                    return back()->with('err', __('Confirm password does not match.'));
                }
            } else {
                return back()->with('err', __('Current password Does not match.'));
            }
        }

        $user->update($input);
        Session::flash('success', 'Successfully change your password');
        return back();
    }

    public function changePass()
    {
        return view('user.changepass');
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'old_password' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ];

        $messages = [
            'password.required' => __('The new password field is required'),
            'password.confirmed' => __("Password does'nt match"),
            'password_confirmation.required' => __("The password confirmation field is required."),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();

            if (!Hash::check($request->old_password, $staff->password)) {
                $validator->errors()->add('oldPassMatch', true);

                return redirect()->route('user.changePass')
                    ->withErrors($validator);
            }

            $staff = Staff::findOrFail($staff->id);
            $staff->password = bcrypt($request->password);
            $staff->save();

            Session::flash('success', __('Password changed successfully'));
            return redirect()->back();
        }

        // if given old password matches with the password of this authenticated user...
        if (Hash::check($request->old_password, Auth::guard('web')->user()->password)) {
            $oldPassMatch = 'matched';
        } else {
            $oldPassMatch = 'not_matched';
        }
        if ($validator->fails() || $oldPassMatch == 'not_matched') {
            if ($oldPassMatch == 'not_matched') {
                $validator->errors()->add('oldPassMatch', true);
            }
            return redirect()->route('user.changePass')
                ->withErrors($validator);
        }

        // updating password in database...
        $user = App\Models\User::findOrFail(Auth::guard('web')->user()->id);
        $user->password = bcrypt($request->password);
        $user->save();

        Session::flash('success', __('Password changed successfully'));
        return redirect()->back();
    }

    public function shippingdetails()
    {
        $user = Auth::user();
        return view('user.shipping_details', compact('user'));
    }

    public function shippingupdate(Request $request)
    {
        $request->validate([
            "shpping_fname" => 'required',
            "shpping_lname" => 'required',
            "shpping_email" => 'required',
            "shpping_number" => 'required',
            "shpping_city" => 'required',
            "shpping_state" => 'required',
            "shpping_address" => 'required',
            "shpping_country" => 'required',
        ]);

        Auth::user()->update($request->all());
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function billingdetails()
    {
        $user = Auth::user();
        return view('user.billing_details', compact('user'));
    }

    public function billingupdate(Request $request)
    {
        $request->validate([
            "billing_fname" => 'required',
            "billing_lname" => 'required',
            "billing_email" => 'required',
            "billing_number" => 'required',
            "billing_city" => 'required',
            "billing_state" => 'required',
            "billing_address" => 'required',
            "billing_country" => 'required',
        ]);

        Auth::user()->update($request->all());

        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function changeTheme(Request $request)
    {
        return redirect()->back()->withCookie(cookie()->forever('user-theme', $request->theme));
    }

    public function pdfTest()
    {
        $pdf = Pdf::loadView('frontend.shop.invoice', compact());
        return $pdf->stream('invoice.pdf');
    }
}
