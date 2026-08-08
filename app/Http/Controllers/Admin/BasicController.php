<?php

namespace App\Http\Controllers\Admin;

use App\Models\Seo;
use App\Models\Page;
use App\Models\Heading;
use App\Models\Language;
use App\Models\Timezone;
use App\Models\BasicSetting;
use Illuminate\Http\Request;
use ParagonIE\Sodium\Compat;
use App\Models\BasicExtended;
use App\Rules\ImageMimeTypeRule;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BasicController extends Controller
{

    public function setLocaleAdmin(Request $request)
    {
        Session::put('admin_lang', 'admin_' . $request->code);
        app()->setLocale('admin_' . $request->code);
        return $request->code;
    }

    public function basicinfo()
    {

        $data['abs'] = BasicSetting::firstOrFail();
        $data['abe'] = BasicExtended::firstOrFail();
        $data['timezones'] = Timezone::all();
        return view('admin.basic.basicinfo', $data);
    }

    public function updatebasicinfo(Request $request)
    {
        $bsSettings = BasicSetting::first();
        $request->validate([
            'website_title' => 'required',
            'timezone' => 'required',
            'base_color' => 'required',
            'base_color2' => 'required',
            'base_currency_symbol' => 'required',
            'base_currency_symbol_position' => 'required',
            'base_currency_text' => 'required',
            'base_currency_text_position' => 'required',
            'base_currency_rate' => 'required|numeric',
            'preloader_status' => 'required',
            'preloader' => [
                is_null($bsSettings->preloader)  ? 'required' : 'nullable',
                new ImageMimeTypeRule()
            ],
            'favicon' => [
                is_null($bsSettings->favicon)  ? 'required' : 'nullable',
                new ImageMimeTypeRule()
            ],
            'logo' => [
                is_null($bsSettings->logo)  ? 'required' : 'nullable',
                new ImageMimeTypeRule()
            ]
        ]);

        $logoName  = $bsSettings->logo;
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoName = uniqid() . '.' . $logoFile->getClientOriginalExtension();
            $logoFile->move(public_path('assets/front/img/'), $logoName);
        }


        $preloaderName  = $bsSettings->preloader;
        if ($request->hasFile('preloader')) {
            $preloaderFile = $request->file('preloader');
            $preloaderName = uniqid() . '.' . $preloaderFile->getClientOriginalExtension();
            $preloaderFile->move(public_path('assets/front/img/'), $preloaderName);
        }

        $faviconName  = $bsSettings->favicon;
        if ($request->hasFile('favicon')) {
            $faviconFile = $request->file('favicon');
            $faviconName = uniqid() . '.' . $faviconFile->getClientOriginalExtension();
            $faviconFile->move(public_path('assets/front/img/'), $faviconName);
        }

        $bss = BasicSetting::all();
        foreach ($bss as $key => $bs) {
            $bs->website_title = $request->website_title;
            $bs->base_color = $request->base_color;
            $bs->base_color2 = $request->base_color2;
            $bs->preloader_status = $request->preloader_status;
            $bs->preloader = $preloaderName;
            $bs->favicon = $faviconName;
            $bs->logo = $logoName;
            $bs->save();
        }

        $bes = BasicExtended::all();
        foreach ($bes as $key => $be) {
            $be->base_currency_symbol = $request->base_currency_symbol;
            $be->base_currency_symbol_position = $request->base_currency_symbol_position;
            $be->base_currency_text = $request->base_currency_text;
            $be->base_currency_text_position = $request->base_currency_text_position;
            $be->base_currency_rate = $request->base_currency_rate;
            $be->timezone = $request->timezone;
            $be->save();
        }
        // set timezone in .env
        if ($request->has('timezone') && $request->filled('timezone')) {
            $arr = ['TIMEZONE' => $request->timezone];
            setEnvironmentValue($arr);
            Artisan::call('config:clear');
        }
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function updateslider(Request $request, $lang)
    {
        $be = BasicExtended::where('language_id', $lang)->firstOrFail();
        if ($request->hasFile('slider_shape_img')) {
            @unlink(public_path('assets/front/img/' . $be->slider_shape_img));
            $filename = uniqid() . '.' . $request->slider_shape_img->getClientOriginalExtension();
            $request->slider_shape_img->move(public_path('assets/front/img/'), $filename);
            $be->slider_shape_img = $filename;
        }

        if ($request->hasFile('slider_bottom_img')) {
            @unlink(public_path('assets/front/img/' . $be->slider_bottom_img));
            $filename = uniqid() . '.' . $request->slider_bottom_img->getClientOriginalExtension();
            $request->slider_bottom_img->move(public_path('assets/front/img/'), $filename);
            $be->slider_bottom_img = $filename;
        }

        $be->save();
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function breadcrumb(Request $request)
    {
        return view('admin.basic.breadcrumb');
    }

    public function updatebreadcrumb(Request $request)
    {
        $img = $request->file('file');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $rules = [
            'file' => [
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    if (!empty($img)) {
                        $ext = $img->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg image is allowed"));
                        }
                    }
                },
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        if ($request->hasFile('file')) {
            $filename = uniqid() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('assets/front/img/'), $filename);
            $bss = BasicSetting::all();
            foreach ($bss as $key => $bs) {
                @unlink(public_path('assets/front/img/' . $bs->breadcrumb));
                $bs->breadcrumb = $filename;
                $bs->save();
            }
        }
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function script()
    {
        $data = BasicSetting::first();
        $demoMode = env('DEMO_MODE', 'active');
        $ai_api_key = $demoMode == 'active' ? 'your_api_key' : $data->ai_api_key;
        return view('admin.basic.scripts', compact('data', 'ai_api_key'));
    }

    public function updatescript(Request $request)
    {

        $request->validate([
            'ai_name' => 'required',
            'ai_model_name' => 'required',
            'ai_api_key' => 'required',
            'admin_total_ai_token' => 'required',
        ]);

        $bss = BasicSetting::all();
        foreach ($bss as $bs) {
            $bs->tawkto_chat_link = $request->tawkto_chat_link;
            $bs->is_tawkto = $request->is_tawkto;
            $bs->is_disqus = $request->is_disqus;
            $bs->is_user_disqus = $request->is_user_disqus;
            $bs->disqus_shortname = $request->disqus_shortname;
            $bs->is_recaptcha = $request->is_recaptcha;
            $bs->google_recaptcha_site_key = $request->google_recaptcha_site_key;
            $bs->google_recaptcha_secret_key = $request->google_recaptcha_secret_key;
            $bs->is_whatsapp = $request->is_whatsapp;
            $bs->whatsapp_number = $request->whatsapp_number;
            $bs->whatsapp_header_title = $request->whatsapp_header_title;
            $bs->whatsapp_popup_message = Purifier::clean($request->whatsapp_popup_message, 'youtube');
            $bs->whatsapp_popup = $request->whatsapp_popup;

            $bs->ai_name = $request->ai_name;
            $bs->ai_model_name = $request->ai_model_name;
            $bs->ai_api_key = $request->ai_api_key;
            $bs->admin_total_ai_token = $request->admin_total_ai_token;
            $bs->save();
        }
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function maintainance()
    {
        $data = BasicSetting::select('maintainance_mode', 'maintenance_img', 'maintenance_status', 'maintainance_text', 'secret_path')
            ->first();
        return view('admin.basic.maintainance', ['data' => $data]);
    }

    public function updatemaintainance(Request $request)
    {
        $img = $request->file('file');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $rules = [
            'file' => [
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    if (!empty($img)) {
                        $ext = $img->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg image is allowed"));
                        }
                    }
                },
            ],
            'maintenance_status' => 'required',
            'maintainance_text' => 'required',
        ];
        $message = [
            'maintainance_text.required' => __('The maintenance message field is required.'),
        ];
        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $bs = BasicSetting::first();
        // first, get the maintenance image from db
        if ($request->hasFile('file')) {
            $filename = uniqid() . '.' . $request->file('file')->getClientOriginalExtension();
            @unlink(public_path("assets/front/img/" . $bs->maintenance_img));
            $request->file('file')->move(public_path('assets/front/img/'), $filename);
        }
        $down = "down";
        if ($request->filled('secret_path')) {
            $down .= " --secret=" . $request->secret_path;
        }
        if ($request->maintenance_status == 1) {
            @unlink('core/storage/framework/down');
            Artisan::call('up');
            Artisan::call($down);
        } else {
            Artisan::call('up');
        }
        $bs->update([
            'maintenance_img' => $request->hasFile('file') ? $filename : $bs->maintenance_img,
            'maintenance_status' => $request->maintenance_status,
            'maintainance_text' => Purifier::clean($request->maintainance_text, 'youtube'),
            'secret_path' => $request->secret_path,
        ]);
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function sections(Request $request)
    {
        $data['abs'] = BasicSetting::first();
        $data['hasIntroSectionColumn'] = Schema::hasColumn('basic_settings', 'intro_section');

        if (! is_null($data['abs']->additional_section_status) && $data['abs']->additional_section_status != "null") {
            $data['additional_section_statuses'] = json_decode($data['abs']->additional_section_status, true);
        } else {
            $data['additional_section_statuses'] = [];
        }
        $data['languge_id'] = Language::where([['is_default', 1]])->first()->id;

        return view('admin.home.sections', $data);
    }

    public function updatesections(Request $request)
    {
        $bss = BasicSetting::all();

        // Update only section fields that actually exist in the current database schema.
        $sectionFields = [
            'home_section',
            'partners_section',
            'process_section',
            'features_section',
            'platform_modules_section',
            'pricing_section',
            'faq_section',
            'testimonial_section',
            'top_footer_section',
            'copyright_section',
            'additional_sections',
        ];

        $in = [];
        foreach ($sectionFields as $field) {
            if (Schema::hasColumn('basic_settings', $field) && $request->has($field)) {
                $in[$field] = $request->input($field);
            }
        }

        if (Schema::hasColumn('basic_settings', 'additional_section_status')) {
            $in['additional_section_status'] = json_encode($request->additional_sections, true);
        }

        foreach ($bss as $key => $bs) {
            $bs->update($in);
        }
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function cookiealert(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abe'] = $lang->basic_extended;
        return view('admin.basic.cookie', $data);
    }

    public function updatecookie(Request $request, $langid)
    {
        $rules = [
            'cookie_alert_status' => 'required',
            'cookie_alert_text' => 'required',
            'cookie_alert_button_text' => 'required|max:25',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();
        $be->cookie_alert_status = $request->cookie_alert_status;
        $be->cookie_alert_text = Purifier::clean($request->cookie_alert_text, 'youtube');
        $be->cookie_alert_button_text = $request->cookie_alert_button_text;
        $be->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function seo(Request $request)
    {
        // first, get the language info from db
        $language = Language::where('code', $request->language)->firstOrFail();
        $langId = $language->id;


        // then, get the seo info of that language from db
        $seo = Seo::where('language_id', $langId);

        if ($seo->count() == 0) {
            // if seo info of that language does not exist then create a new one
            Seo::create($request->except('language_id') + [
                'language_id' => $langId,
            ]);
        }
        $information['abe'] = $language->basic_extended;
        $information['abs'] = $language->basic_setting;

        $information['language'] = $language;
        // then, get the seo info of that language from db
        $information['data'] = $seo->first();
        // get all the languages from db
        $information['langs'] = Language::all();
        $information['pages'] = Page::where('language_id', $langId)->get();
        $information['decodedKeywords'] = isset($information['data']->custome_page_meta_keyword) ? json_decode($information['data']->custome_page_meta_keyword, true) : '';
        $information['decodedDescriptions'] = isset($information['data']->custome_page_meta_description) ? json_decode($information['data']->custome_page_meta_description, true) : '';

        return view('admin.basic.seo', $information);
    }

    public function updateSEO(Request $request)
    {

        // first, get the language info from db
        $language = Language::where('code', $request->language)->first();
        $langId = $language->id;
        // then, get the seo info of that language from db
        $seo = SEO::where('language_id', $langId)->first();
        // else update the existing seo info of that language
        $seo->update($request->all());
        Session::flash('success', __('Updated Successfully'));
        return redirect()->back();
    }
    public function aboutSectionInfo()
    {

        $data['abs'] = BasicSetting::first();
        if (!is_null($data['abs']->about_additional_section_status) && $data['abs']->about_additional_section_status != "null") {
            $data['additional_section_statuses'] = json_decode($data['abs']->about_additional_section_status, true);
        } else {
            $data['additional_section_statuses'] = [];
        }
        $language = Language::where('is_default', 1)->first();
        $data['languge_id'] = $language->id;
        return view('admin.about.sections', $data);
    }

    public function aboutSectionInfoUpdate(Request $request)
    {

        $bss = BasicSetting::all();
        $in = $request->all();
        $in['about_additional_section_status'] = json_encode($request->additional_sections, true);
        unset($in['additional_sections']);
        foreach ($bss as $key => $bs) {
            $bs->update($in);
        }
        Session::flash('success', __('Updated Successfully'));
        return back();
    }
    public function heading(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['abe'] = $lang->basic_extended;
        $data['abs'] = $lang->basic_setting;
        $data['language'] = $lang;
        $data['heading'] = Heading::where('language_id', $data['language']->id)->first();
        $data['decodedHeadings'] = isset($data['heading']->custom_page_heading) ? json_decode($data['heading']->custom_page_heading, true) : '';
        $data['pages'] = Page::where('language_id', $data['language']->id)->get();
        return view('admin.heading.index', $data);
    }
    public function update_heading(Request $request)
    {

        $data = [
            'pricing_title' => request('pricing_title'),
            'faq_title' => request('faq_title'),
            'contact_title' => request('contact_title'),
            'blog_title' => request('blog_title'),
            'login_title' => request('login_title'),
            'reset_password_title' => request('reset_password_title'),
            'signup_title' => request('signup_title'),
            'checkout_title' => request('checkout_title'),
            'custom_page_heading' => json_encode(request('custom_page_heading')),
            'listing_title' => request('listing_title'),
            'forget_password_title' => request('forget_password_title'),
            'language_id' => $request->language_id
        ];
        Heading::updateOrInsert(['language_id' => $request->language_id], $data);
        session()->flash('success', __('Update successfully'));
        return 'success';
    }

    public function error_404(Request $request)
    {
        $language = Language::where('code', $request->language)
            ->firstOrFail();

        $data['language'] = $language;

        $data['data'] = BasicExtended::query()
            ->where('language_id', $language->id)
            ->first();
        return view('admin.error_404', $data);
    }
    public function updateError_404(Request $request)
    {

        $language = Language::where('code', $request->language)->firstOrFail();
        $be = BasicExtended::query()
            ->where('language_id', $language->id)
            ->first();

        $rules = [
            'page_not_found_title' => 'required | max:255',
            'page_not_found_subtitle' => 'required | max:255',
            'page_not_found_image' => [is_null($be->page_not_found_image) ? 'required' : 'nullable', new ImageMimeTypeRule()],
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $page_not_found_image = $be->page_not_found_image;
        if ($request->hasFile('page_not_found_image')) {
            $page_not_found_image = uniqid() . '.' . $request->file('page_not_found_image')->getClientOriginalExtension();
            @unlink(public_path("assets/front/img/" . $be->page_not_found_image));
            $request->file('page_not_found_image')->move(public_path('assets/front/img/'), $page_not_found_image);
        }

        BasicExtended::query()->updateOrInsert(
            ['language_id' => $language->id],
            [
                'page_not_found_title' => $request->page_not_found_title,
                'page_not_found_subtitle' => $request->page_not_found_subtitle,
                'page_not_found_image' => $page_not_found_image,
            ]
        );

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }
}
