<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Helpers\Uploader;
use App\Models\PlatformModule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PlatformModuleController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;

        $data['platformModules'] = PlatformModule::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();
        $data['lang_id'] = $lang_id;

        return view('admin.home.platform-module.index', $data);
    }

    public function create(Request $request)
    {
        $languageCode = $request->language;
        if (empty($languageCode)) {
            $languageCode = Language::where('is_default', 1)->value('code');
        }

        return redirect()->route('admin.platform_module.index', ['language' => $languageCode]);
    }

    public function store(Request $request)
    {
        $icon = $request->file('icon');
        $image = $request->file('image');
        $allowedExts = ['jpg', 'png', 'jpeg', 'svg'];
        $messages = [
            'language_id.required' => __('The language field is required'),
        ];

        $rules = [
            'language_id' => 'required',
            'title' => 'required|max:255',
            'subtitle' => 'required|max:255',
            'serial_number' => 'required|integer',
            'icon' => [
                'required',
                function ($attribute, $value, $fail) use ($icon, $allowedExts) {
                    if (!empty($icon)) {
                        $ext = $icon->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg, svg image is allowed"));
                        }
                    }
                },
            ],
            'image' => [
                'required',
                function ($attribute, $value, $fail) use ($image, $allowedExts) {
                    if (!empty($image)) {
                        $ext = $image->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg, svg image is allowed"));
                        }
                    }
                },
            ],
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $module = new PlatformModule();
        $module->language_id = $request->language_id;
        $module->title = $request->title;
        $module->subtitle = $request->subtitle;
        $module->serial_number = $request->serial_number;

        if ($request->hasFile('icon')) {
            $module->icon = Uploader::upload_picture(public_path('assets/front/img/platform_modules'), $icon);
        }

        if ($request->hasFile('image')) {
            $module->image = Uploader::upload_picture(public_path('assets/front/img/platform_modules'), $image);
        }

        $module->save();

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $icon = $request->file('icon');
        $image = $request->file('image');
        $allowedExts = ['jpg', 'png', 'jpeg', 'svg'];

        $rules = [
            'platform_module_id' => 'required',
            'title' => 'required|max:255',
            'subtitle' => 'required|max:255',
            'serial_number' => 'required|integer',
            'icon' => [
                'nullable',
                function ($attribute, $value, $fail) use ($icon, $allowedExts) {
                    if (!empty($icon)) {
                        $ext = $icon->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg, svg image is allowed"));
                        }
                    }
                },
            ],
            'image' => [
                'nullable',
                function ($attribute, $value, $fail) use ($image, $allowedExts) {
                    if (!empty($image)) {
                        $ext = $image->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__("Only png, jpg, jpeg, svg image is allowed"));
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

        $module = PlatformModule::findOrFail($request->platform_module_id);
        $module->title = $request->title;
        $module->subtitle = $request->subtitle;
        $module->serial_number = $request->serial_number;

        if ($request->hasFile('icon')) {
            $module->icon = Uploader::update_picture(public_path('assets/front/img/platform_modules'), $icon, $module->icon);
        }

        if ($request->hasFile('image')) {
            $module->image = Uploader::update_picture(public_path('assets/front/img/platform_modules'), $image, $module->image);
        }

        $module->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $module = PlatformModule::findOrFail($request->platform_module_id);
        if (!empty($module->icon)) {
            Uploader::remove(public_path('assets/front/img/platform_modules'), $module->icon);
        }
        if (!empty($module->image)) {
            Uploader::remove(public_path('assets/front/img/platform_modules'), $module->image);
        }
        $module->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $module = PlatformModule::findOrFail($id);
            if (!empty($module->icon)) {
                Uploader::remove(public_path('assets/front/img/platform_modules'), $module->icon);
            }
            if (!empty($module->image)) {
                Uploader::remove(public_path('assets/front/img/platform_modules'), $module->image);
            }
            $module->delete();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
