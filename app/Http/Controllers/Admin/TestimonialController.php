<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Models\Testimonial;
use App\Models\BasicSetting;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Models\BasicSetting as BS;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        $data['testimonials'] = Testimonial::where('language_id', $data['lang_id'])->orderBy('id', 'DESC')->get();
        return view('admin.home.testimonial.index', $data);
    }

    public function edit($id)
    {
        $data['testimonial'] = Testimonial::findOrFail($id);
        return view('admin.home.testimonial.edit', $data);
    }

    public function store(Request $request)
    {
        $img = $request->file('image');
        $allowedExts = array('jpg', 'png', 'jpeg');
        $messages = [
            'language_id.required' => __('The language field is required')
        ];

        $rules = [
            'language_id' => 'required',
            'comment' => 'required',
            'name' => 'required|max:50',
            'rank' => 'required|max:50',
            'serial_number' => 'required|integer',
            'image' => [
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

        $input = $request->all();
        if ($request->hasFile('image')) {
            $main_image = time() . '.' . $img->getClientOriginalExtension();
            $request->file('image')->move(public_path('assets/front/img/testimonials/'), $main_image);
            $input['image'] = $main_image;
        }

        $testimonial = new Testimonial;
        $testimonial->create($input);
        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $img = $request->file('image');
        $allowedExts = array('jpg', 'png', 'jpeg');

        $rules = [
            'comment' => 'required',
            'name' => 'required|max:50',
            'rank' => 'required|max:50',
            'serial_number' => 'required|integer',
            'image' => [
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

        $input = $request->all();
        $testimonial = Testimonial::findOrFail($request->testimonial_id);
        if ($request->hasFile('image')) {
            $main_image = time() . '.' . $img->getClientOriginalExtension();
            @unlink(public_path('assets/front/img/testimonials/' . $testimonial->image));
            $request->file('image')->move(public_path('assets/front/img/testimonials/'), $main_image);
            $input['image'] = $main_image;
        }

        $testimonial->update($input);
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function textupdate(Request $request, $langid)
    {
        $request->validate([
            'testimonial_section_title' => 'required|max:25'
        ]);
        $bs = BS::where('language_id', $langid)->firstOrFail();
        $bs->testimonial_title = $request->testimonial_section_title;
        if ($request->hasFile('testimonial_bg_img')) {
            $be = BasicExtended::where('language_id', $langid)->firstOrFail();
            $filename = time() . '.' . $request->testimonial_bg_img->getClientOriginalExtension();
            $request->file('testimonial_bg_img')->move(public_path('assets/front/img/'), $filename);
            @unlink(public_path('assets/front/img/' . $be->testimonial_bg_img));
            $be->testimonial_bg_img = $filename;
            $be->save();
        }
        $bs->save();
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function delete(Request $request)
    {
        $testimonial = Testimonial::findOrFail($request->testimonial_id);
        @unlink(public_path('assets/front/img/testimonials/' . $testimonial->image));
        $testimonial->delete();

        Session::flash('success', __('Deleted Successfully'));
        return back();
    }
}
