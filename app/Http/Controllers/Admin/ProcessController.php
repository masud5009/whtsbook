<?php

namespace App\Http\Controllers\Admin;

use App\Models\Process;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Helpers\Uploader;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProcessController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;
        $data['processes'] = Process::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();
        $data['lang_id'] = $lang_id;
        return view('admin.home.process.index', $data);
    }

    public function edit($id)
    {
        $data['process'] = Process::findOrFail($id);
        return view('admin.home.process.edit', $data);
    }

    public function store(Request $request)
    {
        $messages = [
            'language_id.required' => __('The language field is required')
        ];
        $rules = [
            'language_id' => 'required',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
            'title' => 'required|max:100',
            'text' => 'required|max:255',
            'serial_number' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $imageName = Uploader::upload_picture('assets/front/img/process/', $request->image);

        $process = new Process;
        $process->image = $imageName;
        $process->language_id = $request->language_id;
        $process->title = $request->title;
        $process->text = $request->text;
        $process->serial_number = $request->serial_number;
        $process->save();

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => 'required|max:100',
            'text' => 'required',
            'serial_number' => 'required|integer',

        ];

        if ($request->hasFile('image')) {
            $rules['image'] = 'mimes:jpg,jpeg,png|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $process = Process::findOrFail($request->process_id);
        $imageName = $process->image;
        if ($request->hasFile('image') && !is_null($request->old_image)) {
            $imageName = Uploader::update_picture('assets/front/img/process/', $request->old_image, $request->image);
        }

        if (is_null($imageName)) {
            $imageName = Uploader::upload_picture('assets/front/img/process/', $request->image);
        }

        $process->title = $request->title;
        $process->image = $imageName;
        $process->text = $request->text;
        $process->serial_number = $request->serial_number;
        $process->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $process = Process::findOrFail($request->process_id);
        $process->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function removeImage(Request $request)
    {
        $type = $request->type;
        $featId = $request->process_id;
        $process = Process::findOrFail($featId);
        if ($type == "process") {
            @unlink(public_path("assets/front/img/process/" . $process->image));
            $process->image = NULL;
            $process->save();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
