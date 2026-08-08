<?php

namespace App\Services\Room;

use App\Models\User\Room;
use App\Constants\Constant;
use Illuminate\Support\Str;
use App\Models\User\Language;
use App\Http\Helpers\Uploader;
use App\Models\User\RoomBooking;
use App\Models\User\RoomContent;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Auth;

class CategoryService
{

    public function storeData($request)
    {
        $room = new Room();
        $room->slider_imgs = json_encode($request['slider_images']);
        $room->status = $request->status;
        $room->bed = $request->bed;
        $room->bath = $request->bath;
        $room->room_details_page = $request->room_details_page;

        $room->regular_price = $request->regular_price;
        $room->weekend_price = $request->weekend_price;
        $room->seasonal_price = $request->seasonal_price;
        $room->seasonal_weekend_price = $request->seasonal_weekend_price;
        $room->details_link = $request->details_link;

        $room->weekend = $request->weekend_price ? $request->selected_days : NULL;
        $room->seasonal_dates = $request->seasonal_price ? $request->seasonal_dates : NULL;
        $room->seasonal_weekend = $request->selected_seasonal_days ?? NULL;

        $room->adult = $request->adult;
        $room->child = $request->child;
        $room->payment_system = $request->payment_system;
        $room->advance_amount = $request->advance_amount;
        $room->user_id = Auth::guard('web')->user()->id;
        $room->amenities_index = json_encode($request['amenities']);
        $room->save();

        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        foreach ($languages as $language) {
            $code = $language->code;
            if (
                $request->input($code . '_title') ||
                $request->input($code . '_summary') ||
                $request->input($code . '_description') ||
                $request->input($code . '_meta_keywords') ||
                $request->input($code . '_meta_description')
            ) {
                $roomContent = new RoomContent();
                $roomContent->language_id = $language->id;
                $roomContent->room_id = $room->id;
                $roomContent->title = $request[$code . '_title'];
                $roomContent->slug =   Str::slug($request[$code . '_title']);
                $roomContent->summary = Purifier::clean($request[$code . '_summary'], 'youtube');
                $roomContent->description = Purifier::clean($request[$code . '_description'], 'youtube');
                $roomContent->meta_keywords = $request[$code . '_meta_keywords'];
                $roomContent->meta_description = $request[$code . '_meta_description'];
                $roomContent->user_id = Auth::guard('web')->user()->id;
                $roomContent->save();
            }
        }
        return;
    }


    public function updateData($request, $id)
    {
        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $room = Room::findOrFail($id);

        // merge slider images with existing images if request has new slider image
        if ($request->slider_images) {
            $prevImages = json_decode($room->slider_imgs, true);
            $newImages = $request['slider_images'];
            $imgArr = array_merge($prevImages, $newImages);
        }

        $room->update([
            'slider_imgs' => !empty($imgArr) ? json_encode($imgArr) : $room->slider_imgs,
            'status' => $request->status,
            'bed' => $request->bed,
            'bath' => $request->bath,
            'room_details_page' => $request->room_details_page,
            'regular_price' => $request->regular_price,
            'weekend_price' => $request->weekend_price,
            'seasonal_price' => $request->seasonal_price,
            'seasonal_weekend_price' => $request->seasonal_weekend_price,
            'details_link' => $request->details_link,
            'adult' => $request->adult,
            'child' => $request->child,
            'payment_system' => $request->payment_system,
            'advance_amount' => $request->advance_amount,
            'user_id' => Auth::guard('web')->user()->id,
            'amenities_index' => json_encode($request->amenities),
            'weekend' =>  $request->weekend_price ? $request->selected_days : NULL,
            'seasonal_dates' =>  $request->seasonal_price ? $request->seasonal_dates : NULL,
            'seasonal_weekend' => $request->selected_seasonal_days ?? NULL,
        ]);

        foreach ($languages as $language) {
            $code = $language->code;
            $roomContent = RoomContent::where('room_id', $id)
                ->where('language_id', $language->id)
                ->first();
            $hasExistingContent = RoomContent::where('room_id', $id)
                ->where('language_id', $language->id)
                ->exists();

            if (
                $hasExistingContent ||
                $request->input($code . '_title') ||
                $request->input($code . '_summary') ||
                $request->input($code . '_description') ||
                $request->input($code . '_meta_keywords') ||
                $request->input($code . '_meta_description')
            ) {
                $content = [
                    'language_id' => $language->id,
                    'room_id' => $id,
                    'title' => $request[$code . '_title'],
                    'slug' => Str::slug($request[$code . '_title']),
                    'user_id' => Auth::guard('web')->user()->id,
                    'summary' => Purifier::clean($request[$code . '_summary'], 'youtube'),
                    'description' => Purifier::clean($request[$code . '_description'], 'youtube'),
                    'meta_keywords' => $request[$code . '_meta_keywords'],
                    'meta_description' => $request[$code . '_meta_description']
                ];

                if (!empty($roomContent)) {
                    $roomContent->update($content);
                } else {
                    RoomContent::create($content);
                }
            }
        }
        return;
    }


    public function deleteData($id)
    {
        $room = Room::findOrFail($id);
        if ($room->roomContent()->count() > 0) {
            $contents = $room->roomContent()->get();
            foreach ($contents as $content) {
                $content->delete();
            }
        }

        $roomBooking = RoomBooking::where('room_category_id', $id)
            ->where('user_id', Auth::guard('web')->user()->id)
            ->get();
        if ($roomBooking->count() > 0) {
            foreach ($roomBooking as $roomB) {
                // first, delete the attachment
                Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_ATTACHMENTS),  $roomB->attachment);
                // second, delete the invoice
                Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE),  $roomB->invoice);
                $roomB->delete();
            }
        }

        if (!is_null($room->slider_imgs)) {
            $images = json_decode($room->slider_imgs);
            foreach ($images as $image) {
                $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
                $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
                $directory = $isVideo ? Constant::WEBSITE_ROOM_VIDEO : Constant::WEBSITE_ROOM_SLIDER_IMAGE;
                Uploader::remove(public_path($directory), $image);
            }
        }

        Uploader::remove(public_path(Constant::WEBSITE_ROOM_IMAGE), $room->featured_img);
        $room->delete();
    }
}
