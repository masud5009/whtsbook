<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Models\AboutGalleryImage;
use Mews\Purifier\Facades\Purifier;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AboutPageController extends Controller
{
    public function imgtext(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abe'] = $lang->basic_extended;
        $data['abs'] = $lang->basic_setting;
        $data['aboutGalleryImages'] = AboutGalleryImage::where('language_id', $lang->id)
            ->orderBy('serial_number', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.about.img-text', $data);
    }

    public function update(Request $request, $langid)
    {
        $rules = [
            'about_features_section_title' => 'nullable|max:255',
            'about_features_section_subtitle' => 'nullable|max:255',
            'about_features_section_text' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
           return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // about header section
        $be->about_features_section_title = $request->about_features_section_title;
        $be->about_features_section_subtitle = $request->about_features_section_subtitle;
        $be->about_features_section_text = Purifier::clean($request->about_features_section_text, 'youtube');
        $be->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function galleryUpload(Request $request, $langid)
    {
        $img = $request->file('image');
        $allowedExts = ['jpg', 'png', 'jpeg'];
        $rules = [
            'image' => [
                'required',
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    if (!empty($img)) {
                        $ext = strtolower($img->getClientOriginalExtension());
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

        $directory = public_path('assets/front/img/about-gallery/');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = uniqid() . '.' . $img->getClientOriginalExtension();
        $img->move($directory, $filename);

        $gallery = AboutGalleryImage::create([
            'language_id' => $langid,
            'image' => $filename,
            'serial_number' => 0,
        ]);

        return response()->json([
            'id' => $gallery->id,
            'image' => $gallery->image,
            'image_url' => asset('assets/front/img/about-gallery/' . $gallery->image),
        ]);
    }

    public function galleryDelete($id)
    {
        $galleryImage = AboutGalleryImage::findOrFail($id);
        @unlink(public_path('assets/front/img/about-gallery/' . $galleryImage->image));
        $galleryImage->delete();

        return response()->json(['message' => __('Deleted Successfully')]);
    }
}
