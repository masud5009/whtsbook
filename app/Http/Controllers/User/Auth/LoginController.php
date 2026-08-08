<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Language;
use App\Models\BasicSetting as BS;
use App\Models\Heading;
use App\Models\Seo;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout', 'userLogout']]);
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
        return view('front.auth.login', $data);
    }

    public function login(Request $request)
    {
        if (Session::has('link')) {
            $redirectUrl = Session::get('link');
            Session::forget('link');
        } else {
            $redirectUrl = route('user-dashboard');
        }
        //--- Validation Section
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $bs = $currentLang->basic_setting;
        $be = $currentLang->basic_extended;

        $rules = [
            'email'   => 'required|email',
            'password' => 'required'
        ];

        if ($bs->is_recaptcha == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }
        $messages = [
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'g-recaptcha-response.captcha' => __('Captcha error! try again later or contact site admin.'),
        ];
        $request->validate($rules, $messages);
        //--- Validation Section Ends

        // Attempt to log the user in
        if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password])) {

            if (session()->has('lang')) {
                $currentLang = Language::where('code', session()->get('lang'))->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }
            $be = $currentLang->basic_extended;
            // if smtp is active then check email verified or not
            if ($be->is_smtp == 1) {
                if (Auth::guard('web')->user()->email_verified == 0 || Auth::guard('web')->user()->email_verified == 0) {
                    Auth::guard('web')->logout();
                    return back()->with('err', __('Your Email is not Verified!'));
                }
            }

            if (Auth::guard('web')->user()->status == '0') {
                Auth::guard('web')->logout();
                return back()->with('err', __('Your account has been banned'));
            }
            return redirect($redirectUrl);
        }
        // if unsuccessful, then redirect back to the login with the form data
        return back()->with('err', __("Credentials Does not Match!"))->withInput();
    }

    public function logout()
    {
        $wasStaffLoggedIn = Auth::guard('staff')->check();
        Auth::guard('staff')->logout();
        Auth::guard('web')->logout();

        if ($wasStaffLoggedIn) {
            return redirect()->route('staff.login');
        }

        return redirect('/');
    }
}
