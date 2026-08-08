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

class FooterController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        return view('admin.footer.logo-text', $data);
    }

    public function update(Request $request, $langid)
    {
        $img = $request->file('file');
        $allowedExts = array('jpg', 'png', 'jpeg');

        $rules = [
            'footer_text' => 'nullable|max:255',
            'copyright_text' => 'nullable',
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
            'useful_links_title' => 'nullable|max:50',
            'newsletter_title' => 'nullable|max:50'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $bs = BS::where('language_id', $langid)->firstOrFail();
        if ($request->hasFile('file')) {
            @unlink(public_path('assets/front/img/' . $bs->footer_logo));
            $filename = uniqid() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('assets/front/img/'), $filename);
            $bs->footer_logo = $filename;
        }
        $bs->footer_text = $request->footer_text;
        $bs->useful_links_title = $request->useful_links_title;
        $bs->newsletter_title = $request->newsletter_title;
        $bs->newsletter_subtitle = $request->newsletter_subtitle;
        $bs->copyright_text = Purifier::clean($request->copyright_text, 'youtube');
        $bs->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function removeImage(Request $request)
    {
        $type = $request->type;
        $langid = $request->language_id;
        $be = BasicExtended::where('language_id', $langid)->firstOrFail();
        if ($type == "bottom") {
            @unlink(public_path("assets/front/img/" . $be->footer_bottom_img));
            $be->footer_bottom_img = NULL;
            $be->save();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
