<?php

namespace App\Http\Controllers\User;

use App\Models\User\Room;
use App\Constants\Constant;
use Illuminate\Http\Request;
use App\Models\User\Language;
use App\Http\Helpers\Uploader;
use App\Models\User\RoomReview;
use App\Models\User\RoomAmenity;
use App\Models\User\RoomBooking;
use App\Models\User\RoomContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\LanguageStoreRequest;


class LanguageController extends Controller
{
    public function index($lang = false)
    {
        $data['languages'] = Language::query()->where('user_id', Auth::guard('web')->user()->id)->get();
        return view('user.language.index', $data);
    }

    public function store(LanguageStoreRequest $request)
    {
        $customerLangKeywords = file_get_contents(resource_path('lang/customer-lang.json'));
        Language::create([
            'code' => $request->code,
            'name' => $request->name,
            'rtl' => $request->rtl,
            'keywords' => $customerLangKeywords,
            'added_type' => 'user',
            'user_id' => Auth::guard('web')->user()->id
        ]);
        Session::flash('success', __('Created Successfully'));
        return 'success';
    }

    public function makeDefault($id)
    {
        // first, make other languages to non-default language
        Language::where('is_default', 1)->where('user_id', Auth::guard('web')->user()->id)->update(['is_default' => 0]);
        // second, make the selected language to default language
        $language = Language::where('user_id', Auth::guard('web')->user()->id)->findOrFail($id);
        $language->update(['is_default' => 1]);
        return back()->with('success', $language->name . ' ' . 'is set as default language.');
    }
    public function makeDashboardDefault($id)
    {
        Language::where('dashboard_default', 1)->where('user_id', Auth::guard('web')->user()->id)->update(['dashboard_default' => 0]);
        $language = Language::where('user_id', Auth::guard('web')->user()->id)->findOrFail($id);
        $language->update(['dashboard_default' => 1]);
        Session::put('user_lang', $language->code);
        return back()->with('success', $language->name . ' ' . 'is set as default language.');
    }
    public function edit($id)
    {

        if ($id > 0) {
            $data['language'] = Language::where('user_id', Auth::guard('web')->user()->id)->where('id', $id)->firstOrFail();
        }
        $data['id'] = $id;
        return view('user.language.edit', $data);
    }

    public function update(Request $request)
    {
        $language = Language::where('user_id', Auth::guard('web')->user()->id)->findOrFail($request->language_id);

        $rules = [
            'name' => 'required|max:255',
            'code' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($language, $request) {
                    $langs = Language::where('user_id', Auth::guard('web')->user()->id)->where('id', '<>', $language->id)->get();
                    foreach ($langs as $key => $lang) {
                        if ($lang->code == $request->code) {
                            return $fail(__("Language code have to be unique"));
                        }
                    }
                }
            ],
            'rtl' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        $language->name = $request->name;
        $language->code = $request->code;
        $language->rtl = $request->rtl;
        $language->user_id = Auth::guard('web')->user()->id;
        $language->save();
        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    public function editKeyword($id)
    {

        $la = Language::where('user_id', Auth::guard('web')->user()->id)->where('id', $id)->firstOrFail();
        $keywords = json_decode($la->keywords, true);

        return view('user.language.edit_keyword', compact('la', 'keywords'));
    }

    public function updateKeyword(Request $request, $id)
    {
        $lang = Language::query()->where('user_id', Auth::guard('web')->user()->id)->where('id', $id)->firstOrFail();
        $keywords = $request->except('_token');
        $lang->keywords = json_encode($keywords['keys']);
        $lang->save();

        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    public function destroy($id)
    {
        $language = Language::where('user_id', Auth::guard('web')->user()->id)->findOrFail($id);

        if ($language->is_default == 1) {
            return back()->with('warning', __('Default language cannot be delete.'));
        } else {
            DB::transaction(function () use ($language) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                $roomContents = RoomContent::where('language_id', $language->id);
                if ($roomContents->count() > 0) {
                    foreach ($roomContents->get() as $rc) {

                        // if this room has no room_contents of other languages except the selected one,
                        // then delete the room...
                        $otherRcs = RoomContent::where('language_id', '<>', $language->id)->where('room_id', $rc->room_id)->count();
                        if ($otherRcs == 0) {
                            $room = Room::findOrFail($rc->room_id);
                            Uploader::remove(public_path(Constant::WEBSITE_ROOM_IMAGE), $room->featured_img);

                            if (!empty($room->slider_imgs) && $room->slider_imgs != '[]') {
                                $sliders = json_decode($room->slider_imgs, true);
                                foreach ($sliders as $key => $slider) {
                                    Uploader::remove(public_path(Constant::WEBSITE_ROOM_SLIDER_IMAGE), $slider);
                                }
                            }

                            // delete room bookings
                            $rbookings = RoomBooking::where('room_id', $room->id);
                            if ($rbookings->count() > 0) {
                                foreach ($rbookings->get() as $key => $rb) {
                                    Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE), $rb->invoice);
                                    Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_ATTACHMENTS), $rb->attachment);
                                    $rb->delete();
                                }
                            }

                            // delete room ratings
                            RoomReview::where('room_id', $room->id)->delete();

                            $room->delete();
                        }
                        $rc->delete();
                    }
                }

                $amms = RoomAmenity::where('language_id', $language->id);
                if ($amms->count() > 0) {
                    $amms->delete();
                }
            });

            // then, delete the info from db
            $language->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('success', __('Deleted Successfully'));
        }
    }

    public function rtlcheck($langid)
    {

        if ($langid > 0) {
            $lang = Language::where('user_id', Auth::guard('web')->user()->id)->find($langid);
        } else {
            return 0;
        }
        return $lang->rtl;
    }
}
