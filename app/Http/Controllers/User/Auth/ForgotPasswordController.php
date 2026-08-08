<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\Seo;
use App\Models\User;
use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Helpers\MegaMailer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Config;
use App\Models\BasicSetting as BS;
use App\Models\Heading;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('setlang');
        $bs = BS::first();
        Config::set('captcha.sitekey', $bs->google_recaptcha_site_key);
        Config::set('captcha.secret', $bs->google_recaptcha_secret_key);
       
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return
     */
    public function showLinkRequestForm()
    {
       
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['seo'] = Seo::where('language_id', $currentLang->id)->first();
        $data['heading'] = Heading::where('language_id', $currentLang->id)->first();
        return view('front.auth.passwords.email', $data);
    }

    public function sendResetLinkEmail(Request $request)
    {

        $rules = [
            'email' => 'required|email',
        ];
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $bs = $currentLang->basic_setting;
        if ($bs->is_recaptcha == 1) {
            $rules['g-recaptcha-response'] = 'required|captcha';
        }
        $messages = [
            'g-recaptcha-response.required' => __('Please verify that you are not a robot.'),
            'g-recaptcha-response.captcha' => __('Captcha error! try again later or contact site admin.'),
        ];
        $request->validate($rules, $messages);
        $user =  User::query()->where('email', '=', $request->email)->count();
        
        if ($user > 0) {
            // user found
            $user = User::query()->where('email', '=', $request->email)->first();

            if (session()->has('lang')) {
                $currentLang = Language::where('code', session()->get('lang'))->first();
            } else {
                $currentLang = Language::where('is_default', 1)->first();
            }

            $bs = $currentLang->basic_setting;

            $pass_token = Str::random(30);
            $input['pass_token'] = $pass_token;
            $user->update($input);
            $forgetLinkRoute = route('user.reset.password.form', ['pass_token' => $user->pass_token]);
            $btn = "<a href='$forgetLinkRoute'>Please click this link to create a new password.</a>";
            
            $mailer = new MegaMailer();
            $data = [
                'toMail' => $user->email,
                'toName' => $user->username,
                'customer_name' => $user->username,
                'password_reset_link' => $btn,
                'templateType' => 'reset_password',
                'website_title' => $bs->website_title,
                'type' => 'reset_password'
            ];
            $mailer->mailFromUser($data, $user);

            Session::flash('success',__('New password create link sent successfully via mail'));
        } else {
            // user not found
            Session::flash('error', __('No Account Found With This Email.'));
        }

        return back();
    }

    public function passwordCreateForm()
    {
        return view('front.auth.passwords.reset');
    }

    public function createNewPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8',
        ]);
        $user = User::where('pass_token', $request->pass_token)->first();
        if ($user) {
            $user->password = bcrypt($request->password);
            $user->pass_token = null;
            $user->save();
            return back()->with('success', __('New password created successfully'));
        }
        session()->flash('warning', __('This link has expired. Please request it again.'));
        return back();
    }
}
