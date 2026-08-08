<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ulink;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UlinkController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;
        $data['aulinks'] = Ulink::where('language_id', $lang_id)->get();
        $data['lang_id'] = $lang_id;
        return view('admin.footer.ulink.index', $data);
    }

    public function edit($id)
    {
        $data['ulink'] = Ulink::findOrFail($id);
        return view('admin.footer.ulink.edit', $data);
    }

    public function store(Request $request)
    {
        $messages = [
            'language_id.required' => __('The language field is required')
        ];
        $rules = [
            'language_id' => 'required',
            'name' => 'required|max:255',
            'url' => 'required|max:255'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
         return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $ulink = new Ulink;
        $ulink->language_id = $request->language_id;
        $ulink->name = $request->name;
        $ulink->url = $request->url;
        $ulink->save();

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'url' => 'required|max:255'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $ulink = Ulink::findOrFail($request->ulink_id);
        $ulink->name = $request->name;
        $ulink->url = $request->url;
        $ulink->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $ulink = Ulink::findOrFail($request->ulink_id);
        $ulink->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }
}
