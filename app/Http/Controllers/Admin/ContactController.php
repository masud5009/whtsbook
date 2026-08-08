<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\BasicSetting;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        if (empty($request->language)) {
            $data['lang_id'] = 0;
            $data['abs'] = BasicSetting::firstOrFail();
            $data['abe'] = BasicExtended::firstOrFail();
        } else {
            $lang = Language::where('code', $request->language)->firstOrFail();
            $data['lang_id'] = $lang->id;
            $data['abs'] = $lang->basic_setting;
            $data['abe'] = $lang->basic_extended;
        }
        return view('admin.contact', $data);
    }

    public function update(Request $request, $langid)
    {
        $rules = [
            'contact_form_title' => 'required|max:255',
            'contact_info_title' => 'required|max:255',
            'contact_text' => 'required|max:255',
            'contact_addresses' => 'required',
            'contact_numbers' => 'required',
            'contact_mails' => 'required',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->contact_form_title = $request->contact_form_title;
        $bs->contact_info_title = $request->contact_info_title;
        $bs->contact_text = $request->contact_text;
        $bs->latitude = $request->latitude;
        $bs->longitude = $request->longitude;
        $bs->save();


        $be = BasicExtended::where('language_id', $langid)->firstOrFail();
        $be->contact_addresses =  Purifier::clean($request->contact_addresses, 'youtube');
        $be->contact_numbers = $request->contact_numbers;
        $be->contact_mails = $request->contact_mails;
        $be->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }
}
