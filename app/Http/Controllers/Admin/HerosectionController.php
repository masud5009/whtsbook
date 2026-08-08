<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Models\BasicSetting as BS;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class HerosectionController extends Controller
{
    public function imgtext(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abe'] = $lang->basic_extended;
        $data['abs'] = $lang->basic_setting;
        return view('admin.home.hero.img-text', $data);
    }

    public function update(Request $request, $langid)
    {
        $featuresImage = $request->file('image');
        $platformModulesSectionBgImage = $request->file('platform_modules_section_bg_image');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $rules = [
            'image' => [
                function ($attribute, $value, $fail) use ($request, $featuresImage, $allowedExts) {
                    if ($request->hasFile('image')) {
                        $ext = $featuresImage->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg image is allowed"));
                        }
                    }
                },
            ],
            'features_title' => 'nullable|max:255',
            'features_subtitle' => 'nullable|max:255',
            'platform_modules_section_title' => 'nullable|max:255',
            'platform_modules_section_bg_image' => [
                function ($attribute, $value, $fail) use ($request, $platformModulesSectionBgImage, $allowedExts) {
                    if ($request->hasFile('platform_modules_section_bg_image')) {
                        $ext = $platformModulesSectionBgImage->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg image is allowed"));
                        }
                    }
                },
            ],
            'hero_section_subtitle' => 'nullable|max:255',
            'hero_section_text' => 'nullable|max:255',
            'hero_section_button_text' => 'nullable|max:30',
            'hero_section_button_url' => 'nullable',
            'intro_title' => 'nullable|max:255',
            'intro_subtitle' => 'nullable|max:255',
            'intro_text' => 'nullable',
            'intro_section_button_text' => 'nullable|max:255',
            'intro_section_button_url' => 'nullable|max:255',
            'intro_section_video_url' => 'nullable|max:255',
            'work_process_title' => 'nullable|max:255',
            'partner_title' => 'nullable|max:255',
            'pricing_title' => 'nullable|max:255',
            'testimonial_title' => 'nullable|max:255',
            'faq_title' => 'nullable|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
        return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $bs = BS::where('language_id', $langid)->firstOrFail();
        //features section
        $input['features_title'] = $request->features_title;
        $input['features_subtitle'] = $request->features_subtitle;
        $input['faq_title'] = $request->faq_title;

        if ($request->hasFile('image')) {
            @unlink(public_path('assets/front/img/' . $bs->features_image));
            $filename = uniqid() . '.' . $featuresImage->getClientOriginalExtension();
            $featuresImage->move(public_path('assets/front/img/'), $filename);
            $input['features_image'] = $filename;
        }

        //platform modules section
        $input['platform_modules_section_title'] = $request->platform_modules_section_title;
        if ($request->hasFile('platform_modules_section_bg_image')) {
            @unlink(public_path('assets/front/img/' . $bs->platform_modules_section_bg_image));
            $filename = uniqid() . '.' . $platformModulesSectionBgImage->getClientOriginalExtension();
            $platformModulesSectionBgImage->move(public_path('assets/front/img/'), $filename);
            $input['platform_modules_section_bg_image'] = $filename;
        }


        //sections title
        $input['work_process_title'] = $request->work_process_title;
        $input['partner_title'] = $request->partner_title;
        $input['pricing_title'] = $request->pricing_title;
        $input['testimonial_title'] = $request->testimonial_title;
        $input['faq_title'] = $request->faq_title;
        $bs->update($input);


        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

}
