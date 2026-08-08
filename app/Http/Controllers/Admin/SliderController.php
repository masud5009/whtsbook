<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\HeroSlider;
use Illuminate\Http\Request;
use App\Http\Helpers\Uploader;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;

        $data['sliders'] = HeroSlider::where('language_id', $lang_id)
            ->orderBy('serial_number', 'ASC')
            ->get();
        $data['lang_id'] = $lang_id;

        return view('admin.home.slider.index', $data);
    }

    public function create(Request $request)
    {
        $languageCode = $request->language;
        if (empty($languageCode)) {
            $languageCode = Language::where('is_default', 1)->value('code');
        }

        return redirect()->route('admin.slider.index', ['language' => $languageCode]);
    }

    public function store(Request $request)
    {
        $img = $request->file('img');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $messages = [
            'language_id.required' => __('The language field is required')
        ];

        $rules = [
            'language_id' => 'required',
            'title' => 'required|max:255',
            'subtitle' => 'required|max:255',
            'description' => 'required',
            'btn_name' => 'nullable|max:255',
            'btn_url' => 'nullable|max:255',
            'serial_number' => 'required|integer',
            'img' => [
                'required',
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

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
          return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $slider = new HeroSlider;
        $slider->language_id = $request->language_id;
        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->description = $request->description;
        $slider->btn_name = $request->btn_name;
        $slider->btn_url = $request->btn_url;
        $slider->serial_number = $request->serial_number;

        if ($request->hasFile('img')) {
            $directory = 'assets/front/img/hero_slider';
            $slider->img = Uploader::upload_picture(public_path($directory), $img);
        }
        $slider->save();

        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $img = $request->file('img');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $rules = [
            'slider_id' => 'required',
            'title' => 'required|max:255',
            'subtitle' => 'required|max:255',
            'description' => 'required',
            'btn_name' => 'nullable|max:255',
            'btn_url' => 'nullable|max:255',
            'serial_number' => 'required|integer',
            'img' => [
                'nullable',
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

        $slider = HeroSlider::findOrFail($request->slider_id);
        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->description = $request->description;
        $slider->btn_name = $request->btn_name;
        $slider->btn_url = $request->btn_url;
        $slider->serial_number = $request->serial_number;

        if ($request->hasFile('img')) {
            $directory = 'assets/front/img/hero_slider';
            $slider->img = Uploader::update_picture(public_path($directory), $img, $slider->img);
        }
        $slider->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $slider = HeroSlider::findOrFail($request->slider_id);
        if (!empty($slider->img)) {
            Uploader::remove(public_path('assets/front/img/hero_slider'), $slider->img);
        }
        $slider->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $slider = HeroSlider::findOrFail($id);
            if (!empty($slider->img)) {
                Uploader::remove(public_path('assets/front/img/hero_slider'), $slider->img);
            }
            $slider->delete();
        }

        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }
}
