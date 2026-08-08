<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Models\BasicSetting as BS;
use App\Models\Heading;
use App\Models\Language;
use App\Models\Seo;
use App\Models\User;
use App\Models\User\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class StaffLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:staff', ['except' => ['logout']]);
        $this->middleware('setlang');

        $bs = BS::first();

        Config::set('captcha.sitekey', $bs->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs->google_recaptcha_secret_key);
    }

    public function showLoginForm()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();

        return view('front.auth.staff-login', $data);
    }

    public function login(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $bs = $currentLang->basic_setting;

        $rules = [
            'login' => 'required|string',
            'password' => 'required|string',
        ];

        if ($bs->is_recaptcha == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }

        $messages = [
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'g-recaptcha-response.captcha' => __('Captcha error! try again later or contact site admin.'),
        ];

        $request->validate($rules, $messages);

        $staff = Staff::query()
            ->with('roleInfo')
            ->where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if (empty($staff) || !Hash::check($request->password, $staff->password)) {
            return back()->with('err', __('Credentials Does not Match!'))->withInput();
        }

        if (empty($staff->roleInfo)) {
            return back()->with('err', __('No role has been assigned to this staff account yet.'))->withInput();
        }

        $owner = User::find($staff->user_id);

        if (empty($owner) || (int) $owner->status !== 1) {
            return back()->with('err', __('The owner account is not active right now.'))->withInput();
        }

        Auth::guard('staff')->login($staff);
        Auth::guard('web')->login($owner);
        $request->session()->regenerate();

        return redirect()->route('user-dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
