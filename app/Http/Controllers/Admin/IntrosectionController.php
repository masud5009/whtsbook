<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\BasicSetting as BS;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class IntrosectionController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        return view('admin.home.intro-section', $data);
    }

    public function update(Request $request, $langid)
    {
        $input = $request->all();
        $bs = BS::where('language_id', $langid)->firstOrFail();
        $input['intro_text'] = Purifier::clean($request->intro_text, 'youtube');
        $bs->update($input);

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function removeImage(Request $request)
    {
        $type = $request->type;
        $langid = $request->language_id;
        $bs = BS::where('language_id', $langid)->firstOrFail();
        if ($type == "signature") {
            @unlink(public_path("assets/front/img/" . $bs->intro_signature));
            $bs->intro_signature = NULL;
            $bs->save();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
