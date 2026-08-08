<?php

namespace App\Http\Controllers\User;

use App\Models\Timezone;
use App\Models\User\SEO;
use App\Constants\Constant;
use App\Enums\ThemeVersion;
use Illuminate\Http\Request;
use App\Models\User\Language;
use App\Http\Helpers\Uploader;
use App\Rules\ImageMimeTypeRule;
use App\Models\User\BasicSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BasicController extends Controller
{


    public function breadcrumb(Request $request)
    {
        $data['basic_setting'] = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('breadcrumb')->first();
        return view('user.settings.breadcrumb', $data);
    }

    public function updateBreadcrumb(Request $request)
    {
        $bss = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('breadcrumb')->first();

        $rules = [];
        if (!$request->filled('breadcrumb') && is_null($bss->breadcrumb)) {
            $rules['breadcrumb'] = 'required';
        }
        if ($request->hasFile('breadcrumb')) {
            $rules['breadcrumb'] = new ImageMimeTypeRule();
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'id' => 'breadcrumb'], 422);
        }

        if ($request->hasFile('breadcrumb')) {
            $filename = Uploader::update_picture(public_path(Constant::WEBSITE_BREADCRUMB), $request->file('breadcrumb'), $bss->breadcrumb);
            BasicSetting::query()->updateOrInsert(
                ['user_id' => Auth::guard('web')->user()->id],
                ['breadcrumb' => $filename]
            );
        }
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function footerLogo(Request $request)
    {
        $data['basic_setting'] = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('footer_logo')->first();
        return view('user.settings.footer-logo', $data);
    }

    public function updateFooterLogo(Request $request)
    {
        $bss = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('footer_logo')->first();

        $rules = [];
        if (!$request->filled('footer_logo') && is_null($bss->footer_logo)) {
            $rules['footer_logo'] = 'required';
        }
        if ($request->hasFile('footer_logo')) {
            $rules['footer_logo'] = new ImageMimeTypeRule();
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), 'id' => 'footer_logo'], 422);
        }

        if ($request->hasFile('footer_logo')) {
            $filename = Uploader::upload_picture(Constant::WEBSITE_FOOTER_LOGO, $request->file('footer_logo'));
            BasicSetting::query()->updateOrInsert(
                ['user_id' => Auth::guard('web')->user()->id],
                ['footer_logo' => $filename]
            );
        }
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function information()
    {
        $data = BasicSetting::query()->where('user_id', Auth::guard('web')->user()->id)
            ->select('website_title', 'support_email', 'support_contact', 'address', 'latitude', 'longitude')
            ->first();
        return view('user.settings.information', ['data' => $data]);
    }

    public function updateInfo(Request $request)
    {
        $request->validate(
            [
                'support_email' => 'nullable',
                'support_contact' => 'nullable',
                'address' => 'nullable',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]
        );
        BasicSetting::query()->updateOrInsert(
            ['user_id' => Auth::guard('web')->user()->id],
            $request->except(['_token', 'user_id'] + [
                'user_id' => Auth::guard('web')->user()->id,
            ])
        );
        Session::flash('success', __('Updated Successfully'));
        return redirect()->back();
    }

    public function getMailInformation()
    {
        $data['info'] = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('reply_to', 'from_name')->first();
        return view('user.settings.email.mail-information', $data);
    }

    public function storeMailInformation(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'from_name' => 'required',
        ], [
            'email.required' => __('The email field is required'),
            'from_name.required' => __('The from name field is required'),
        ]);
        $info = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();
        $info->reply_to = $request->email;
        $info->from_name = $request->from_name;
        $info->save();
        Session::flash('success', __('Updated Successfully'));
        return back();
    }
    public function generalSettings(Request $request)
    {
        $data['timezones'] = Timezone::all();
        $data['data'] = BasicSetting::query()
            ->where('user_id', Auth::guard('web')->user()->id)
            ->first();
        return view('user.settings.general_settings', $data);
    }

    public function updateGeneralSettings(Request $request)
    {
        $userBs = BasicSetting::query()
            ->where('user_id', Auth::guard('web')->user()->id)
            ->first();
        $rules = [
            'website_title' => 'required',
            'favicon' => [is_null($userBs->favicon) ? 'required' : 'nullable', new ImageMimeTypeRule()],
            'thumbnail_image' => [is_null($userBs->logo) ? 'required' : 'nullable'],
            'preloader' => [
                is_null($userBs->preloader) ? 'required' : 'nullable',
                new ImageMimeTypeRule()
            ],
            'primary_color' => 'required',
            'primary_color' => 'required',
            'secondary_color' => 'required',
            'base_currency_symbol' => 'required',
            'base_currency_symbol_position' => 'required',
            'base_currency_text' => 'required',
            'base_currency_text_position' => 'required',
            'base_currency_rate' => 'required|numeric',
            'timezone' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $favicon = $userBs->favicon;
        if ($request->hasFile('favicon')) {
            $favicon = Uploader::update_picture(
                public_path(Constant::WEBSITE_FAVICON),
                $request->file('favicon'),
                $userBs->favicon
            );
        }
        $preloader = $userBs->preloader;
        if ($request->hasFile('preloader')) {
            $preloader = Uploader::update_picture(
                public_path(Constant::WEBSITE_FAVICON),
                $request->file('preloader'),
                $userBs->preloader
            );
        }
        //thumnail image is logo image
        $logo = $userBs->logo;
        if ($request->hasFile('thumbnail_image')) {
            $logo = Uploader::logo_image(public_path(Constant::WEBSITE_LOGO . '/'), $request->file('thumbnail_image'), $userBs->logo, 322, 115);
        }
        BasicSetting::query()->updateOrInsert(
            ['user_id' => Auth::guard('web')->user()->id],
            [
                'website_title' => $request->website_title,
                'base_currency_symbol' => $request->base_currency_symbol,
                'base_currency_symbol_position' => $request->base_currency_symbol_position,
                'base_currency_text' => $request->base_currency_text,
                'base_currency_text_position' => $request->base_currency_text_position,
                'base_currency_rate' => $request->base_currency_rate,
                'timezone' => $request->timezone,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'favicon' => $favicon,
                'logo' => $logo,
                'preloader' => $preloader,
                'preloader_status' => $request->preloader_status,
            ]
        );
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }
}
