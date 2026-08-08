<?php

namespace App\Http\Controllers\User;

use DateTime;
use Carbon\Carbon;
use App\Services\Common;
use App\Models\User\Room;
use App\Traits\WhatsaApp;
use App\Constants\Constant;
use App\Models\User\Coupon;
use App\Models\User\Refund;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Models\User\Language;
use App\Services\MailService;
use App\Http\Helpers\Uploader;
use App\Models\User\RoomNumber;
use Illuminate\Validation\Rule;
use App\Models\User\RoomAmenity;
use App\Models\User\RoomBooking;
use App\Models\User\RoomContent;
use App\Models\User\BasicSetting;
use App\Traits\MiscellaneousTrait;
use Illuminate\Support\Facades\DB;
use App\Exports\RoomBookingsExport;
use App\Models\User\OfflineGateway;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use App\Models\User\BookingAdjustment;
use App\Models\User\RoomNumberContent;
use App\Services\Room\CategoryService;
use Illuminate\Support\Facades\Config;
use App\Services\WpTemplateMessageSend;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Room\StoreRequest;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Room\UpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Services\BookingAdjustmentService;
use App\Http\Requests\AdminRoomBookingRequest;
use App\Models\User\PaymentGateway as OnlineGateway;


class RoomController extends Controller
{
    use MiscellaneousTrait, WhatsaApp;

    public function settings()
    {
        $data = DB::table('user_basic_settings')
            ->where('user_id', Auth::guard('web')->user()->id)
            ->select(

                'checkin_time',
                'checkout_time',
                'room_booking_cancellation',
                'cancellation_time_limit_hours',
                'cancellation_refund_percentage'
            )
            ->first();

        return view('user.rooms.settings', ['data' => $data]);
    }

    public function updateSettings(Request $request)
    {
        $rules = [
            'checkin_time' => 'required',
            'checkout_time' => 'required',
            'room_booking_cancellation' => 'required',
            'cancellation_time_limit_hours' => 'required_if:room_booking_cancellation,active',
            'cancellation_refund_percentage' => 'required_if:room_booking_cancellation,active'
        ];

        $messages = [
            'checkin_time.required' => 'Check-in time is required.',
            'checkout_time.required' => 'Check-out time is required.',
            'room_booking_cancellation.required' => 'Room booking cancellation status is required.',
            'cancellation_time_limit_hours.required_if' => 'Cancellation time limit is required when booking cancellation is active.',
            'cancellation_time_limit_hours.integer' => 'Cancellation time limit must be a valid number.',
            'cancellation_time_limit_hours.min' => 'Cancellation time limit cannot be negative.',
            'cancellation_refund_percentage.required_if' => 'Refund percentage is required when booking cancellation is active.',
            'cancellation_refund_percentage.integer' => 'Refund percentage must be a valid number.',
            'cancellation_refund_percentage.min' => 'Refund percentage cannot be less than 0.',
            'cancellation_refund_percentage.max' => 'Refund percentage cannot be more than 100.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }
        DB::table('user_basic_settings')->where('user_id', Auth::guard('web')->user()->id)->update([

            'checkin_time' => $request->checkin_time,
            'checkout_time' => $request->checkout_time,
            'tax' => $request->tax,
            'room_booking_cancellation' => $request->room_booking_cancellation,
            'cancellation_time_limit_hours' => $request->cancellation_time_limit_hours,
            'cancellation_refund_percentage' => $request->cancellation_refund_percentage,
        ]);

        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }


    public function coupons()
    {
        // get the coupons from db
        $information['coupons'] = Coupon::orderByDesc('id')->where('user_id', Auth::guard('web')->user()->id)->get();
        // also, get the currency information from db
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();
        $language = Language::where('user_id', Auth::guard('web')->user()->id)->first();
        $rooms = Room::where('user_id', Auth::guard('web')->user()->id)->get();
        $rooms->map(function ($room) use ($language) {
            $room['title'] = $room->roomContent()->where('language_id', $language->id)->pluck('title')->first();
        });
        $information['rooms'] = $rooms;
        return view('user.rooms.coupons', $information);
    }

    public function storeCoupon(CouponRequest $request)
    {

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        if ($request->filled('rooms')) {
            $rooms = $request->rooms;
        }
        Coupon::create($request->except('start_date', 'end_date', 'rooms') + [
            'start_date' => date_format($startDate, 'Y-m-d'),
            'end_date' => date_format($endDate, 'Y-m-d'),
            'rooms' => isset($rooms) ? json_encode($rooms) : null,
            'user_id' => Auth::guard('web')->user()->id
        ]);

        Session::flash('success', __('Created Successfully'));
        return 'success';
    }

    public function updateCoupon(CouponRequest $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        if ($request->filled('rooms')) {
            $rooms = $request->rooms;
        }
        Coupon::find($request->id)->update($request->except('start_date', 'end_date', 'rooms') + [
            'start_date' => date_format($startDate, 'Y-m-d'),
            'end_date' => date_format($endDate, 'Y-m-d'),
            'rooms' => isset($rooms) ? json_encode($rooms) : null
        ]);

        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    public function destroyCoupon($id)
    {
        Coupon::find($id)->delete();
        return redirect()->back()->with('success', __('Deleted Successfully'));
    }

    public function amenities(Request $request)
    {
        // first, get the language info from db
        $language = Language::where('code', $request->language)->where('user_id', Auth::guard('web')->user()->id)->firstOrFail();
        $information['language'] = $language;
        // then, get the room amenities of that language from db
        $information['amenities'] = RoomAmenity::where('language_id', $language->id)
            ->orderBy('id', 'desc')->where('user_id', Auth::guard('web')->user()->id)
            ->get();
        // also, get all the languages from db
        $information['langs'] = Language::where('user_id', Auth::guard('web')->user()->id)->get();

        return view('user.rooms.amenities', $information);
    }

    public function storeAmenity(Request $request)
    {
        $rules = [
            'serial_number' => 'required',
            'status' => 'required'
        ];

        $messages = [];
        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        foreach ($languages as $language) {
            $rules[$language->code . '_name'] = ($language->is_default == 1 ? 'required' : 'nullable') . '|max:255';

            if ($language->is_default == 1) {
                $messages[$language->code . '_name.required'] = __('The name field is required for') . ' ' . $language->name . ' ' . __('language');
            }
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
        $uid = uniqid();
        foreach ($languages as $language) {
            $roomAmenity = new RoomAmenity();
            $roomAmenity->language_id =  $language->id;
            $roomAmenity->user_id =  Auth::guard('web')->user()->id;
            $roomAmenity->name = $request[$language->code . '_name'];
            $roomAmenity->indx  = $uid;
            $roomAmenity->status =  $request->status;
            $roomAmenity->serial_number =  $request->serial_number;
            $roomAmenity->save();
        }

        Session::flash('success', __('Created Successfully'));
        return 'success';
    }

    public function editAmenity($id)
    {
        $userId = Auth::guard('web')->user()->id;
        $data['languages'] = Language::where('user_id', $userId)->get();
        $data['data'] = RoomAmenity::query()
            ->where('user_id', $userId)
            ->findorFail($id);
        return view('user.rooms.edit_amenity', $data);
    }

    public function updateAmenity(Request $request)
    {
        $rules = [
            'serial_number' => 'required'
        ];

        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $messages = [];
        foreach ($languages as $language) {
            $rules[$language->code . '_name'] = ($language->is_default == 1 ? 'required' : 'nullable') . '|max:255';

            if ($language->is_default == 1) {
                $messages[$language->code . '_name.required'] = __('The name field is required for') . '  ' . $language->name . '  ' . __('language');
            }
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        foreach ($languages as $language) {
            $roomAmenity = RoomAmenity::query()
                ->where('user_id', Auth::guard('web')->user()->id)
                ->where('indx', $request->amenity_indx)->where('language_id', $language->id)->first();
            $roomAmenity->name = $request[$language->code . '_name'];
            $roomAmenity->serial_number =  $request->serial_number;
            $roomAmenity->status =  $request->status;
            $roomAmenity->save();
        }
        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    public function deleteAmenity(Request $request)
    {
        RoomAmenity::ownedByUser()->findOrFail($request->amenity_id)->delete();
        Session::flash('success', __('Deleted Successfully'));
        return redirect()->back();
    }

    public function bulkDeleteAmenity(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            RoomAmenity::ownedByUser()->findOrFail($id)->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return 'success';
    }

    public function rooms(Request $request)
    {
        $languageId = Language::where('code', $request->language)->where('user_id', Auth::guard('web')->user()->id)->pluck('id')->first();
        $roomContents = RoomContent::with('room')
            ->where('language_id', '=', $languageId)->where('user_id', Auth::guard('web')->user()->id)
            ->orderBy('room_id', 'desc')
            ->get();

        $currencyInfo = MiscellaneousTrait::getCurrencyInfoUser();
        return view('user.rooms.category.index', compact('roomContents', 'currencyInfo', 'languageId'));
    }

    public function createRoom(Request $request)
    {
        // get all the languages from db
        $information['languages'] = Language::where('user_id', Auth::guard('web')->user()->id)->get();

        return view('user.rooms.category.create', $information);
    }

    public function sliderImgageUpload(Request $request)
    {

        $file = $request->file('slider_images');
        $allowedExts = array('jpg', 'png', 'jpeg', 'mp4', 'webm', 'ogg', 'mov');
        $rules = [
            'slider_images' => [
                function ($attribute, $value, $fail) use ($file, $allowedExts) {
                    if (!$file) {
                        return $fail("File is required");
                    }
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (!in_array($ext, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg images and mp4, webm, ogg, mov videos are allowed");
                    }
                }
            ]
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($request->hasFile('slider_images')) {
            $ext = strtolower($file->getClientOriginalExtension());
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
            $directory = $isVideo ? Constant::WEBSITE_ROOM_VIDEO : Constant::WEBSITE_ROOM_SLIDER_IMAGE;
            $filename = Uploader::upload_picture(public_path($directory), $request->file('slider_images'));
        }
        return response()->json(['status' => 'success', 'file_id' => $filename]);
    }
    public function removeImage(Request $request)
    {
        $img = $request['imageName'];

        try {
            $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
            $directory = $isVideo ? Constant::WEBSITE_ROOM_VIDEO : Constant::WEBSITE_ROOM_SLIDER_IMAGE;
            Uploader::remove(public_path($directory), $img);

            return Response::json(['success' => __('The file has been deleted.')], 200);
        } catch (\Exception $e) {
            return Response::json(['error' => __('Something went wrong!')], 400);
        }
    }

    public function detachImage(Request $request)
    {
        $id = $request['id'];
        $key = $request['key'];
        $room = Room::query()->find($id);
        $sliderImages = json_decode($room->slider_imgs, true);
        if (count($sliderImages) == 1) {
            return Response::json(['message' => __('Sorry, the last image cannot be delete.')], 400);
        } else {
            $image = $sliderImages[$key];
            $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov']);
            $directory = $isVideo ? Constant::WEBSITE_ROOM_VIDEO : Constant::WEBSITE_ROOM_SLIDER_IMAGE;
            Uploader::remove(public_path($directory), $image);
            array_splice($sliderImages, $key, 1);
            $room->update([
                'slider_imgs' => json_encode($sliderImages)
            ]);
            return Response::json(['message' => __('Deleted Successfully')], 200);
        }
    }

    /**
     * store room category
     */
    public function storeRoom(StoreRequest $request, CategoryService $categoryService)
    {
        $categoryService->storeData($request);

        Session::flash('success', __('Created Successfully'));
        return 'success';
    }

    public function editRoom($id)
    {
        $data['languages'] = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $room = Room::findOrfail($id);

        $data['weekendArray'] = Str::of($room->weekend)->explode(',')->filter()->toArray();

        $seasonalDates = json_decode($room->seasonal_dates, true) ?? [];

        // Get day names from seasonal_weekend, not seasonal_dates
        $dayNames = [];
        if (!empty($room->seasonal_weekend)) {
            $dayNames = explode(',', $room->seasonal_weekend);
            $dayNames = array_map('trim', $dayNames);
            $dayNames = array_filter($dayNames);
        }

        $data['seasonalWeekend'] = $dayNames;
        $data['seasonalDates'] = $seasonalDates;
        $data['dayNames'] = $dayNames;

        $data['room'] = $room;
        return view('user.rooms.category.edit', $data);
    }

    /**
     * update room category
     */
    public function updateRoom(UpdateRequest $request, $id, CategoryService $categoryService)
    {
        $categoryService->updateData($request, $id);

        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    /**
     * delete room category
     */
    public function deleteRoom(Request $request, CategoryService $categoryService)
    {
        $categoryService->deleteData($request->room_id);

        Session::flash('success', __('Deleted Successfully'));
        return redirect()->back();
    }

    /**
     * delete room category
     */
    public function bulkDeleteRoom(Request $request, CategoryService $categoryService)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $categoryService->deleteData($id);
        }

        Session::flash('success', __('Deleted Successfully'));
        return 'success';
    }

    public function roomNumbers(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;

        $languages = Language::where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();

        $defaultLanguage = $languages->firstWhere('is_default', 1) ?? $languages->first();
        $selectedLanguage = $languages->firstWhere('code', $request->input('language')) ?? $defaultLanguage;
        $selectedLanguageId = $selectedLanguage?->id;

        $information['webDefaultLang'] = $defaultLanguage?->code;
        $information['selectedLanguageId'] = $selectedLanguageId;
        $information['selectedLanguageCode'] = $selectedLanguage?->code;
        $information['langs'] = $languages;

        $information['roomCategories'] = Room::join('user_room_category_contents', 'user_room_categories.id', '=', 'user_room_category_contents.room_id')
            ->where('user_room_categories.user_id', $userId)
            ->when($selectedLanguageId, function ($query, $selectedLanguageId) {
                return $query->where('user_room_category_contents.language_id', $selectedLanguageId);
            })
            ->select('user_room_categories.*', 'user_room_category_contents.title', 'user_room_category_contents.slug')
            ->orderBy('user_room_categories.id', 'desc')
            ->get();

        $roomNumber = $request->filled('room_number') ? trim($request->room_number) : null;
        $roomCategoryId = $request->filled('room_category_id') ? $request->room_category_id : null;
        $status = $request->filled('status') ? $request->status : null;

        // then, get the room list based on applied filters
        $information['rooms'] = RoomNumber::with([
            'contents' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
            'categoryContents' => function ($query) use ($selectedLanguageId, $userId) {
                $query->where('user_id', $userId)
                    ->when($selectedLanguageId, function ($q, $selectedLanguageId) {
                        return $q->where('language_id', $selectedLanguageId);
                    });
            }
        ])
            ->where('user_id', $userId)
            ->when($roomNumber, function ($query, $roomNumber) use ($selectedLanguageId, $userId) {
                return $query->whereHas('contents', function ($q) use ($roomNumber, $selectedLanguageId, $userId) {
                    $q->where('user_id', $userId)
                        ->where('name', 'like', '%' . $roomNumber . '%')
                        ->when($selectedLanguageId, function ($contentQuery, $selectedLanguageId) {
                            return $contentQuery->where('language_id', $selectedLanguageId);
                        });
                });
            })
            ->when($roomCategoryId, function ($query, $roomCategoryId) {
                return $query->where('room_category_id', $roomCategoryId);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('user.rooms.index', $information);
    }

    public function roomNumberStore(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $languages = Language::where('user_id', $userId)->get();
        $defaultLanguage = $languages->firstWhere('is_default', 1) ?? $languages->first();
        $webDefaultLang = $defaultLanguage?->code;

        // validation
        $rules = [
            'room_category_id' => 'required',
            'status' => 'required',
        ];

        $messages = [
            'room_number_' . $webDefaultLang . '.required' => __('Please provide a room number/name in') . ' ' . $webDefaultLang . ' ' . __('language.'),
            'room_number_' . $webDefaultLang . '.unique' => __('The room number/name has already been taken.'),
            'room_number_' . $webDefaultLang . '.max' => __('The room number/name may not be greater than 255 characters.'),
        ];

        foreach ($languages as $language) {
            $field = 'room_number_' . $language->code;
            $rules[$field] = [
                $language->id == $defaultLanguage?->id ? 'required' : 'nullable',
                'max:255',
                Rule::unique('user_room_contents', 'name')->where(function ($query) use ($userId, $language) {
                    return $query->where('user_id', $userId)
                        ->where('language_id', $language->id);
                }),
            ];

            $messages[$field . '.unique'] = __('The room number/name has already been taken for') . ' ' . $language->name . ' ' . __('language.');
            $messages[$field . '.max'] = __('The room number/name may not be greater than 255 characters.');
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        // store room data
        DB::transaction(function () use ($request, $userId, $languages, $defaultLanguage) {
            $room = RoomNumber::create([
                'user_id' => $userId,
                'language_id' => $defaultLanguage?->id,
                'room_category_id' => $request->room_category_id,
                'status' => $request->status,
            ]);

            // store room number/name content data language-wise
            foreach ($languages as $language) {
                $name = trim((string) $request->input('room_number_' . $language->code));

                RoomNumberContent::create([
                    'room_id' => $room->id,
                    'language_id' => $language->id,
                    'user_id' => $userId,
                    'name' => $name !== '' ? $name : null,
                ]);
            }
        });

        session()->flash('success', 'New room added successfully!');
        return 'success';
    }

    public function roomNumberUpdate(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $roomId = (int) $request->room_id;
        $languages = Language::where('user_id', $userId)->get();
        $defaultLanguage = $languages->firstWhere('is_default', 1) ?? $languages->first();
        $webDefaultLang = $defaultLanguage?->code;

        $rules = [
            'room_category_id' => 'required',
            'status' => 'required'
        ];

        $messages = [
            'room_number_' . $webDefaultLang . '.required' => __('Please provide a room number/name in') . ' ' . $webDefaultLang . ' ' . __('language.'),
            'room_number_' . $webDefaultLang . '.unique' => __('The room number/name has already been taken.'),
            'room_number_' . $webDefaultLang . '.max' => __('The room number/name may not be greater than 255 characters.'),
            'room_category_id.required' => __('Please select a room category.')
        ];

        foreach ($languages as $language) {
            $field = 'room_number_' . $language->code;
            $rules[$field] = [
                $language->id == $defaultLanguage?->id ? 'required' : 'nullable',
                'max:255',
                Rule::unique('user_room_contents', 'name')->where(function ($query) use ($userId, $language, $roomId) {
                    return $query->where('user_id', $userId)
                        ->where('language_id', $language->id)
                        ->where('room_id', '<>', $roomId);
                }),
            ];

            $messages[$field . '.unique'] = __('The room number/name has already been taken for') . ' ' . $language->name . ' ' . __('language.');
            $messages[$field . '.max'] = __('The room number/name may not be greater than 255 characters.');
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $room = RoomNumber::where('user_id', $userId)->findOrFail($roomId);

        DB::transaction(function () use ($request, $userId, $languages, $room) {
            $room->update([
                'room_category_id' => $request->room_category_id,
                'status' => $request->status,
            ]);

            foreach ($languages as $language) {
                $name = trim((string) $request->input('room_number_' . $language->code));

                RoomNumberContent::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'language_id' => $language->id,
                        'user_id' => $userId,
                    ],
                    [
                        'name' => $name !== '' ? $name : null,
                    ]
                );
            }
        });

        session()->flash('success', 'Room updated successfully!');

        return 'success';
    }

    public function roomNumberDelete(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $room = RoomNumber::where('user_id', $userId)->findOrFail($request->room_id);

        DB::transaction(function () use ($room, $userId) {
            RoomNumberContent::where('user_id', $userId)
                ->where('room_id', $room->id)
                ->delete();

            $room->delete();
        });

        session()->flash('success', 'Room deleted successfully!');

        return redirect()->back();
    }

    public function roomNumberBulkDelete(Request $request)
    {
        $ids = $request->ids;
        $userId = Auth::guard('web')->user()->id;

        DB::transaction(function () use ($ids, $userId) {
            foreach ($ids as $id) {
                $room = RoomNumber::where('user_id', $userId)->findOrFail($id);

                RoomNumberContent::where('user_id', $userId)
                    ->where('room_id', $room->id)
                    ->delete();

                $room->delete();
            }
        });

        session()->flash('success', 'Room deleted successfully!');

        /**
         * this 'success' is returning for ajax call.
         * if return == 'success' then ajax will reload the page.
         */
        return 'success';
    }

    /**
     * All bookings display
     */
    public function bookings(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $languages = Language::where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();

        $booking_number = $booking_status = $status = null;

        if ($request->filled('booking_no')) {
            $booking_number = $request['booking_no'];
        }

        if ($request->filled('status')) {
            $status = $request['status'];
        }

        if ($request->routeIs('tenant.room_bookings.approved_bookings')) {
            $booking_status = "approved";
        } elseif ($request->routeIs('tenant.room_bookings.pending_bookings')) {
            $booking_status = "pending";
        } elseif ($request->routeIs('tenant.room_bookings.canceled_bookings')) {
            $booking_status = "canceled";
        }


        $selectedLanguage = $this->languageForRequest($userId, $request, $languages);

        $selectedLanguageId = optional($selectedLanguage)->id;

        $information['language'] = $selectedLanguage;



        $now = Carbon::now(config('app.timezone'));
        $bs = DB::table('user_basic_settings')
            ->where('user_id', $userId)
            ->select(
                'checkin_time',
                'checkout_time',
                'room_booking_cancellation',
                'cancellation_time_limit_hours',
                'cancellation_refund_percentage'
            )
            ->first();

        $checkinTime = $bs->checkin_time;
        $checkOutTime = $bs->checkout_time;

        $bookingQuery = RoomBooking::query()
            ->when($booking_number, function ($query, $booking_number) {
                return $query->where('booking_number', 'like', '%' . $booking_number . '%');
            })
            ->when($request->filled('unassigned') && $request->unassigned == 1, function ($query) {
                return $query->whereNull('reserved_dates_info');
            })
            ->when($request->filled('unassigned') && $request->unassigned == 0, function ($query) {
                return $query->whereNotNull('reserved_dates_info');
            })
            ->when($booking_status, function ($query, $booking_status) {
                if ($booking_status === 'approved') {
                    return $query->where('booking_status', 1);
                } elseif ($booking_status === 'canceled') {
                    return $query->where('booking_status', 2);
                } elseif ($booking_status === 'pending') {
                    return $query->where('booking_status', 0);
                }
                return $query;
            })
            ->when($status, function ($query, $status) {
                if ($status === 'approved') {
                    return $query->where('booking_status', 1);
                } elseif ($status === 'canceled') {
                    return $query->where('booking_status', 2);
                } elseif ($status === 'pending') {
                    return $query->where('booking_status', 0);
                }
                return $query;
            })
            ->when($request->routeIs('tenant.room_bookings.active_bookings'), function ($query) use ($now, $checkinTime, $checkOutTime) {
                return $query->where('booking_status', '!=', 2)
                    ->where('payment_status', '!=', 2)
                    ->whereRaw(
                        "STR_TO_DATE(CONCAT(arrival_date, ' ', ?), '%Y-%m-%d %H:%i:%s') <= ?",
                        [$checkinTime, $now->toDateTimeString()]
                    )
                    ->whereRaw(
                        "STR_TO_DATE(CONCAT(departure_date, ' ', ?), '%Y-%m-%d %H:%i:%s') >= ?",
                        [$checkOutTime, $now->toDateTimeString()]
                    );
            })
            ->where('user_id', $userId);

        $information['isAnyUnassignedRoomAvailable'] = (clone $bookingQuery)
            ->whereNull('reserved_dates_info')
            ->exists();

        $bookings = $bookingQuery
            ->orderBy('id', 'desc')
            ->paginate(10);

        $information['roomInfos'] = $selectedLanguage->roomDetails()
            ->whereHas('room', function (Builder $query) {
                $query->where('status', 1);
            })
            ->select('room_id', 'title')
            ->orderBy('title', 'ASC')
            ->get();

        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();
        $information['cancellationPolicy'] = $bs;

        $information['pageTitle'] = match (true) {
            $request->routeIs('tenant.room_bookings.all_bookings')      => __('All Bookings'),
            $request->routeIs('tenant.room_bookings.approved_bookings') => __('Approved Bookings'),
            $request->routeIs('tenant.room_bookings.pending_bookings')  => __('Pending Bookings'),
            $request->routeIs('tenant.room_bookings.active_bookings')   => __('Active Bookings'),
            $request->routeIs('tenant.room_bookings.canceled_bookings') => __('Canceled Bookings'),
            default => '',
        };

        $information['bookings'] = $bookings;
        $information['langs'] = Language::where('user_id', $userId)->get();

        return view('user.rooms.booking.index', $information);
    }

    /**
     * Today's booked rooms display
     */
    public function todaysBooked(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $language = Language::where('is_default', 1)->where('user_id', $userId)->firstOrFail();

        $now = Carbon::now(config('app.timezone'));
        $today = $now->copy()->startOfDay(); // Start of today

        $bs = DB::table('user_basic_settings')->where('user_id', $userId)->select('checkin_time', 'checkout_time')->first();
        $checkinTime = $bs->checkin_time;
        $checkOutTime = $bs->checkout_time;

        // Create today's checkout datetime using checkout time
        $todayCheckout = Carbon::parse($today->format('Y-m-d') . ' ' . $checkOutTime, config('app.timezone'));

        // If current time is before checkout time, treat it as previous day
        if ($now->lt($todayCheckout)) {
            $today->subDay();
        }

        $today = $today->format('Y-m-d');

        // Step 1: Get the IDs of rooms that are actively booked today
        $bookedRoomIds = DB::table('user_room_bookings')
            ->whereRaw(
                "STR_TO_DATE(CONCAT(arrival_date, ' ', ?), '%Y-%m-%d %H:%i:%s') <= ?",
                [$checkinTime, $now->toDateTimeString()]
            )
            ->whereRaw(
                "STR_TO_DATE(CONCAT(departure_date, ' ', ?), '%Y-%m-%d %H:%i:%s') >= ?",
                [$checkOutTime, $now->toDateTimeString()]
            )
            ->where('booking_status', '!=', 2)
            ->pluck('reserved_dates_info')
            ->flatMap(function ($json) use ($today) {
                $decoded = json_decode($json, true);

                // Skip if JSON is null or not an array
                if (!is_array($decoded)) return [];

                return collect($decoded)
                    ->filter(function ($item) use ($today) {
                        return isset($item['date'], $item['room_id']) && $item['date'] === $today;
                    })
                    ->pluck('room_id');
            })
            ->unique()
            ->values();

        // Step 2: Get the booked room details with room names (based on language)
        $bookedRooms = DB::table('user_rooms as rn')
            ->leftJoin('user_room_category_contents as rc', 'rn.room_category_id', '=', 'rc.room_id')
            ->leftJoin('user_room_contents as rnc', function ($join) use ($language, $userId) {
                $join->on('rn.id', '=', 'rnc.room_id')
                    ->where('rnc.language_id', $language->id)
                    ->where('rnc.user_id', $userId);
            })
            ->where('rn.user_id', $userId)
            ->whereIn('rn.id', $bookedRoomIds)
            ->where('rc.language_id', $language->id)
            ->select('rn.id', 'rn.user_id', 'rn.language_id', 'rn.room_category_id', 'rn.status', 'rc.title as room_name', 'rnc.name as room_number')
            ->get();

        // Step 3: Get the available (not booked) rooms
        $availableRooms = DB::table('user_rooms as rn')
            ->leftJoin('user_room_category_contents as rc', 'rn.room_category_id', '=', 'rc.room_id')
            ->leftJoin('user_room_contents as rnc', function ($join) use ($language, $userId) {
                $join->on('rn.id', '=', 'rnc.room_id')
                    ->where('rnc.language_id', $language->id)
                    ->where('rnc.user_id', $userId);
            })
            ->where('rn.user_id', $userId)
            ->whereNotIn('rn.id', $bookedRoomIds)
            ->where('rc.language_id', $language->id)
            ->select('rn.id', 'rn.user_id', 'rn.language_id', 'rn.room_category_id', 'rn.status', 'rc.title as room_name', 'rnc.name as room_number')
            ->get();

        $information['bookedRoomNumbers'] = $bookedRooms;
        $information['avaiableroomNumbers'] = $availableRooms;
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();

        return view('user.rooms.booking.todays-booked', $information);
    }

    /**
     * update stay status of a booking [upcoming,checked-in]
     */
    public function updateStayStatus(Request $request)
    {
        $roomBooking = RoomBooking::findOrFail($request->booking_id);

        $roomBooking->stay_status = $request->stay_status;

        $roomBooking->save();

        if ($request->stay_status == 'checked-out') {
            session()->flash('warning', 'Please Update The Booking.');
        }

        session()->flash('success', 'Stay status updated successfully!');

        return redirect()->back();
    }


    public function booakings(Request $request)
    {
        $booking_number = null;
        if ($request->filled('booking_no')) {
            $booking_number = $request['booking_no'];
        }

        if (URL::current() == Route::is('tenant.room_bookings.all_bookings')) {
            $information['bookings'] = RoomBooking::when($booking_number, function ($query, $booking_number) {
                return $query->where('booking_number', 'like', '%' . $booking_number . '%');
            })->where('user_id', Auth::guard('web')->user()->id)->orderBy('id', 'desc')
                ->paginate(10);
        }

        $language = Language::query()->where('user_id', Auth::guard('web')->user()->id)->where('is_default', 1)->first();
        $information['roomInfos'] = $language->roomDetails()->whereHas('room', function (Builder $query) {
            $query->where('status', '=', 1)->where('user_id', Auth::guard('web')->user()->id);
        })
            ->select('room_id', 'title')
            ->orderBy('title', 'ASC')
            ->get();
        $information['language']  = $language;
        return view('user.rooms.bookings', $information);
    }

    /**
     * display the details of a booking
     */
    public function roomBookingDetails($id)
    {
        $details = RoomBooking::findOrFail($id);
        $details = $this->syncBookingPricing($details);
        $information['details'] = $details;
        $information['customBookingFields'] = json_decode($details->custom_booking_fields);

        // get the difference of two dates, date should be in 'YYYY-MM-DD' format
        $date1 = new DateTime($details->arrival_date);
        $date2 = new DateTime($details->departure_date);
        $information['interval'] = $date1->diff($date2, true);
        $language = Language::where('is_default', 1)->where('user_id', Auth::guard('web')->user()->id)->first();

        /**
         * to get the room title first get the room info using eloquent relationship
         * then, get the room content info of that room using eloquent relationship
         * after that, we can access the room title
         * also, get the room category using eloquent relationship
         */
        $roomInfo = $details->hotelRoom()->first();

        $information['roomContent'] = $roomInfo->roomContent()
            ->where('language_id', $language->id)
            ->select('title', 'slug')
            ->first();
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();
        $information['userBs'] = BasicSetting::select(
            'room_tax_status',
            'room_tax',
            'room_fee_status',
            'room_fee'
        )->where('user_id', $details->user_id)->first();

        $information['rent'] = $roomInfo->rent;

        $raw = json_decode($details->reserved_dates_info, true) ?? [];
        $information['reserved_dates_info'] = collect($raw)
            ->sortBy('date')
            ->groupBy('date')
            ->map(function ($items, $date) {
                return [
                    'date' => \Carbon\Carbon::parse($date)->format('d M, Y'),
                    'rooms' => $items->map(function ($item) {
                        $room = $item['room_number'] ?? $item['room_no'] ?? $item['room_numberno'] ?? 'N/A';
                        return ['room_number' => $room];
                    })->toArray()
                ];
            })
            ->values();

        $information['bookingAdjustment'] = BookingAdjustment::firstOrCreate(
            ['booking_id' => $details->id],
            [
                'user_id' => $details->user_id,
                'grand_total' => $details->grand_total,
                'amount' => 0,
                'type' => 'initial',
            ]
        );
        $information['refund'] = Refund::where('booking_id', $details->id)->first();

        return view('user.rooms.booking.details', $information);
    }

    /**
     * display the edit page of a booking
     */
    public function roomBookingEdit($id)
    {
        $details = RoomBooking::findOrFail($id);
        $details = $this->syncBookingPricing($details);
        // get the difference of two dates, date should be in 'YYYY-MM-DD' format
        $date1 = new DateTime($details->arrival_date);
        $date2 = new DateTime($details->departure_date);
        $information['interval2'] = $date1->diff($date2, true);
        $userId = Auth::guard('web')->user()->id;
        $language = $this->languageForRequest($userId, request());

        //get room title
        $roomInfo = $details->hotelRoom()->first();
        $information['roomTitle'] = $roomInfo->roomContent()->where('language_id', $language->id)->value('title');

        $start = \Carbon\Carbon::parse($details->arrival_date);
        $end = \Carbon\Carbon::parse($details->departure_date)->subDay();
        $interval = $start->diffInDays($end) + 1;

        $maxRoomsPerDay = (int) $details->total_rooms;
        $roomCategory = Room::findOrFail($details->room_category_id);

        // Step 1: Load all booked room ids by date
        $roomBookings = RoomBooking::where('room_category_id', $details->room_category_id)
            ->where('booking_status', '!=', 2)
            ->whereNotNull('reserved_dates_info')
            ->where('id', '!=', $details->id)
            ->get(['reserved_dates_info']);

        $bookedRoomsByDate = [];
        foreach ($roomBookings as $booking) {
            $reserved = is_string($booking->reserved_dates_info)
                ? json_decode($booking->reserved_dates_info, true)
                : $booking->reserved_dates_info;

            if (!is_array($reserved)) continue;

            foreach ($reserved as $entry) {
                if (!is_array($entry) || !isset($entry['date'])) continue;

                $date = $entry['date'];
                foreach ($this->reservedRoomKeys($entry) as $roomKey) {
                    $bookedRoomsByDate[$date][] = $roomKey;
                }
            }
        }


        $allRooms = $this->activeRoomNumbersForCategory($userId, $details->room_category_id, $language?->id);
        // Price calculation
        $roomPrices = Common::priceCalculation($roomCategory, $details->arrival_date, $details->departure_date);
        // Build priceByDate map
        $reqDatePrices = $roomPrices['dailyDetails'] ?? [];
        $priceByDate = collect($reqDatePrices)->pluck('price', 'date')->toArray();



        // Step 2: Build daily room status
        $selectedRooms = is_string($details->reserved_dates_info)
            ? json_decode($details->reserved_dates_info, true)
            : $details->reserved_dates_info;
        $selectedRooms = is_array($selectedRooms) ? $selectedRooms : [];

        $mySelectedRoomsByDate = [];
        if (!empty($selectedRooms)) {
            foreach ($selectedRooms as $entry) {
                if (!is_array($entry) || !isset($entry['date'])) continue;

                foreach ($this->reservedRoomKeys($entry) as $roomKey) {
                    $mySelectedRoomsByDate[$entry['date']][] = $roomKey;
                }
            }
        }


        $dates = [];
        $loopStart = $start->copy();
        while ($loopStart->lte($end)) {
            $dateStr = $loopStart->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];
            $rentForDate = $priceByDate[$dateStr] ?? $roomCategory->regular_price;

            $rooms = $allRooms->map(function ($room) use ($bookedRoomKeys, $rentForDate, $dateStr, $mySelectedRoomsByDate) {
                $status = $this->roomMatchesAnyKey($room, $bookedRoomKeys) ? 'booked' : 'available';
                // selected check
                $selected = false;
                if ($status !== 'booked') {
                    $selected = $this->roomMatchesAnyKey($room, $mySelectedRoomsByDate[$dateStr] ?? []);
                }

                return [
                    'id'          => $room->id,
                    'room_number' => $this->roomNumberContentName($room),
                    'status'      => $status,
                    'rent'        => $rentForDate,
                    'selected' => $selected,
                ];
            })->values()->toArray();

            $dates[] = [
                'date'  => $dateStr,
                'rooms' => $rooms,
            ];

            $loopStart->addDay();
        }
        // Check if any date has insufficient rooms
        $insufficientDate = null;
        $availableCount = 0;

        $checkDate = $start->copy();
        while ($checkDate->lte($end)) {
            $dateStr = $checkDate->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];

            $availableCount = $allRooms->filter(function ($room) use ($bookedRoomKeys) {
                return !$this->roomMatchesAnyKey($room, $bookedRoomKeys);
            })->count();

            if ($availableCount < $maxRoomsPerDay) {
                $insufficientDate = $dateStr;
                break;
            }

            $checkDate->addDay();
        }
        // get selected room numbers
        $rents = Room::where('id', $roomCategory->id)
            ->select('regular_price', 'weekend_price', 'seasonal_price')
            ->first()
            ?->toArray() ?? [
                'regular_price'  => 0,
                'weekend_price'  => 0,
                'seasonal_price' => 0,
            ];

        $roomList = collect($selectedRooms)
            ->map(function ($entry) {
                if (!is_array($entry)) return null;

                return $this->reservedRoomKeys($entry)[0] ?? null;
            })
            ->filter()
            ->unique()
            ->map(function ($roomKey) use ($allRooms, $rents, $interval) {
                $room = $this->findRoomByKey($allRooms, $roomKey);
                if (!$room) return null;
                return [
                    'room_number' => $this->roomNumberContentName($room),
                    'room_id'     => $room->id,
                    'rent'        => $rents,
                    'days'        => (int) $interval,
                ];
            })
            ->filter()
            ->values()
            ->toArray();



        // get all the booked dates of this room
        $information['onlineGateways'] = OnlineGateway::query()
            ->where('status', '=', 1)
            ->select('name')
            ->get();
        $information['offlineGateways'] = OfflineGateway::query()
            ->where('status', '=', 1)
            ->select('name')
            ->orderBy('serial_number', 'asc')
            ->get();

        $information['interval']  = $interval;
        $information['dates']  = $dates;
        $information['roomList'] = $roomList;
        $information['totalRooms']  = $details->total_rooms;
        $information['discount']  = 0.00;
        $information['insufficientDate']  = $insufficientDate;
        $information['dateStr']  = $dateStr;
        $information['availableCount']  = $availableCount;
        $information['totalRent']        = $roomPrices['totalPrice'] ?? 0;
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();
        $information['userId'] = Auth::guard('web')->user()->id;
        $information['details'] = $details;


        $information['bookingAdjustment'] = BookingAdjustment::where('booking_id', $details->id)->first();

        return view('user.rooms.booking.edit', $information);
    }


    /**
     * Store room booking
     */
    public function makeBooking(AdminRoomBookingRequest  $request)
    {
        $response = RoomBooking::storeBooking($request);

        session()->flash('success', __('Created Successfully'));
        return 'success';
    }

    /**
     * Update room booking and Send payment link with whatsapp
     */
    public function updateBooking(AdminRoomBookingRequest $request)
    {
        $response = RoomBooking::updateBooking($request);

        if ($response['status'] === false) {
            session()->flash('error', $response['message']);
            return 'success';
        }
        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    /**
     * Update extra payment
     */
    public function updateExtraPayment($booking_id)
    {
        $booking = RoomBooking::findOrFail($booking_id);
        BookingAdjustment::where('booking_id', $booking_id)->update([
            'amount' => 0,
            'type' => 'initial',
            'grand_total' => $booking->paid_amount,
        ]);

        $booking->update([
            'payment_status' => 1
        ]);

        session()->flash('success', __('Updated Successfully'));
        return redirect()->back();
    }

    public function checkIn(Request $request)
    {
        $booking_number = $request->filled('booking_no') ? $request->input('booking_no') : null;
        $date_option    = $request->input('date_option', 'today');

        $now = now(config('app.timezone'));

        $bs = DB::table('user_basic_settings')->where('user_id', Auth::guard('web')->user()->id)->select('checkin_time')->first();
        $checkinTime = $bs->checkin_time;

        // arrival_date + checkinTime -> DATETIME
        $checkinExpr = "STR_TO_DATE(CONCAT(arrival_date, ' ', ?), '%Y-%m-%d %H:%i:%s')";

        $bookings = RoomBooking::query()
            ->when(
                $booking_number,
                fn($q) =>
                $q->where('booking_number', 'like', '%' . $booking_number . '%')
            )
            // Common guards
            ->where('booking_status', '!=', 2)
            ->where('payment_status', '!=', 2)
            ->where('stay_status', 'Upcoming')

            ->when($request->routeIs('tenant.room_bookings.check_ins.delayed'), function ($q) use ($request, $date_option, $checkinExpr, $checkinTime, $now) {

                if ($date_option === 'custom') {
                    $request->validate([
                        'start_date' => ['required', 'date_format:Y-m-d'],
                        'end_date'   => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                    ]);

                    $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->toDateString();
                    $end   = Carbon::createFromFormat('Y-m-d', $request->end_date)->toDateString();

                    $q->whereBetween('arrival_date', [$start, $end]);
                    $q->whereRaw("$checkinExpr <= ?", [$checkinTime, $now->toDateTimeString()]);
                } else {
                    $request->validate([
                        'date' => ['nullable', 'date_format:Y-m-d'],
                    ]);

                    $selected = $request->filled('date')
                        ? Carbon::createFromFormat('Y-m-d', $request->date)
                        : now();

                    if ($date_option === 'yesterday') {
                        $selected = now()->subDay();
                    }

                    if ($selected->isFuture()) {
                        return $q->whereRaw('1=0');
                    }

                    $q->whereDate('arrival_date', $selected->toDateString());

                    if ($selected->isToday()) {
                        $q->whereRaw("$checkinExpr <= ?", [$checkinTime, $now->toDateTimeString()]);
                    }
                }
            })

            ->when($request->routeIs('tenant.room_bookings.check_ins.upcoming'), function ($q) use ($request, $date_option, $checkinExpr, $checkinTime, $now) {

                if ($date_option === 'custom') {
                    $request->validate([
                        'start_date' => ['required', 'date_format:Y-m-d'],
                        'end_date'   => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                    ]);

                    $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->toDateString();
                    $end   = Carbon::createFromFormat('Y-m-d', $request->end_date)->toDateString();

                    $q->whereBetween('arrival_date', [$start, $end]);
                    $q->whereRaw("$checkinExpr > ?", [$checkinTime, $now->toDateTimeString()]);
                } else {
                    $request->validate([
                        'date' => ['nullable', 'date_format:Y-m-d'],
                    ]);

                    $selected = $request->filled('date')
                        ? Carbon::createFromFormat('Y-m-d', $request->date)
                        : now();

                    if ($date_option === 'tomorrow') {
                        $selected = now()->addDay();
                    }

                    $q->whereDate('arrival_date', $selected->toDateString());
                    if ($selected->isToday()) {
                        $q->whereRaw("$checkinExpr > ?", [$checkinTime, $now->toDateTimeString()]);
                    }
                }
            })

            ->orderBy('id', 'desc')
            ->paginate(10);

        $information['bookings']     = $bookings;
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();

        return view('user.rooms.booking.check-in', $information);
    }

    public function checkOut(Request $request)
    {
        $booking_number = $request->filled('booking_no') ? $request->input('booking_no') : null;
        $date_option    = $request->input('date_option', 'today');

        $bs = DB::table('user_basic_settings')->where('user_id', Auth::guard('web')->user()->id)->select('checkout_time')->first();
        $checkOutTime = $bs->checkout_time ?? '12:00:00';

        $now = now(config('app.timezone'));

        // departure_date + checkoutTime -> DATETIME
        $checkoutExpr = "STR_TO_DATE(CONCAT(departure_date, ' ', ?), '%Y-%m-%d %H:%i:%s')";

        $bookings = RoomBooking::query()
            ->when(
                $booking_number,
                fn($q) =>
                $q->where('booking_number', 'like', '%' . $booking_number . '%')
            )
            // Common guards
            ->where('booking_status', '!=', 2)
            ->where('payment_status', '!=', 2)
            ->where('stay_status', 'checked-in')


            ->when($request->routeIs('tenant.room_bookings.check_outs.delayed'), function ($q) use ($request, $checkoutExpr, $checkOutTime, $now, $date_option) {

                if ($date_option === 'custom') {
                    $request->validate([
                        'start_date' => ['required', 'date_format:Y-m-d'],
                        'end_date'   => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                    ]);

                    $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->toDateString();
                    $end   = Carbon::createFromFormat('Y-m-d', $request->end_date)->toDateString();

                    $q->whereBetween('departure_date', [$start, $end]);
                    $q->whereRaw("$checkoutExpr <= ?", [$checkOutTime, $now->toDateTimeString()]);
                } else {
                    // specific day: today | yesterday
                    $request->validate([
                        'date' => ['nullable', 'date_format:Y-m-d'],
                    ]);
                    $selected = $request->filled('date')
                        ? Carbon::createFromFormat('Y-m-d', $request->date)
                        : now();

                    if ($date_option === 'yesterday') {
                        $selected = now()->subDay();
                    }

                    $q->whereDate('departure_date', $selected->toDateString());

                    if ($selected->isToday()) {
                        $q->whereRaw("$checkoutExpr <= ?", [$checkOutTime, $now->toDateTimeString()]);
                    }
                    if ($selected->isFuture()) {
                        $q->whereRaw('1=0');
                    }
                }
            })

            /* 🟢 UPCOMING: specific(today/tomorrow) + custom-range */
            ->when($request->routeIs('tenant.room_bookings.check_outs.upcoming'), function ($q) use ($request, $checkoutExpr, $checkOutTime, $now, $date_option) {

                if ($date_option === 'custom') {
                    $request->validate([
                        'start_date' => ['required', 'date_format:Y-m-d'],
                        'end_date'   => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                    ]);

                    $start = Carbon::createFromFormat('Y-m-d', $request->start_date)->toDateString();
                    $end   = Carbon::createFromFormat('Y-m-d', $request->end_date)->toDateString();

                    $q->whereBetween('departure_date', [$start, $end]);
                    $q->whereRaw("$checkoutExpr > ?", [$checkOutTime, $now->toDateTimeString()]);
                } else {
                    $request->validate([
                        'date' => ['nullable', 'date_format:Y-m-d'],
                    ]);

                    $selected = $request->filled('date')
                        ? Carbon::createFromFormat('Y-m-d', $request->date)
                        : now();

                    if ($date_option === 'tomorrow') {
                        $selected = now()->addDay();
                    }

                    $q->whereDate('departure_date', $selected->toDateString());

                    if ($selected->isToday()) {
                        $q->whereRaw("$checkoutExpr > ?", [$checkOutTime, $now->toDateTimeString()]);
                    }
                }
            })

            ->orderBy('id', 'desc')
            ->paginate(10);

        $information['bookings']     = $bookings;
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();

        return view('user.rooms.booking.check-out', $information);
    }

    public function sendMail(Request $request)
    {

        $rules = [
            'subject' => 'required',
            'message' => 'required',
        ];
        $messages = [
            'subject.required' => __('The email subject field is required.'),
            'message.required' => __('The email message field is required.')
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $be = BasicExtended::first();
        if (empty($be->smtp_host) || empty($be->smtp_port) || empty($be->encryption) || empty($be->smtp_username) || empty($be->smtp_password)) {
            return back();
        }

        $userBs = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();


        if ($be->is_smtp == 1) {
            try {
                $smtp = [
                    'transport' => 'smtp',
                    'host' => $be->smtp_host,
                    'port' => $be->smtp_port,
                    'encryption' => $be->encryption,
                    'username' => $be->smtp_username,
                    'password' => $be->smtp_password,
                    'timeout' => null,
                    'auth_mode' => null,
                ];
                Config::set('mail.mailers.smtp', $smtp);
            } catch (\Exception $e) {
                Session::flash('error', $e->getMessage());
                return back();
            }
        }

        try {
            Mail::send([], [], function ($message) use ($request, $be, $userBs) {
                $fromMail = $be->from_mail;
                $fromName = $userBs->from_name;
                $message->to($request->customer_email)
                    ->subject($request->subject)
                    ->from($fromMail, $fromName)
                    ->replyTo($userBs->reply_to, $userBs->from_name)
                    ->html($request->message, 'text/html');
            });
            Session::flash('success', __('Mail sent successfully'));
            return 'success';
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            return back();
        }
    }

    public function deleteBooking(Request $request, $id)
    {
        $roomBooking = RoomBooking::findOrFail($id);
        // first, delete the attachment
        Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_ATTACHMENTS), $roomBooking->attachment);
        // second, delete the invoice
        Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE), $roomBooking->invoice);

        // third, delete the room booking adjustments
        BookingAdjustment::where('booking_id', $roomBooking->id)->delete();

        // finally, delete the room booking record from db
        $roomBooking->delete();
        Session::flash('success', __('Room booking record deleted successfully!'));
        return redirect()->back();
    }

    public function bulkDeleteBooking(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $roomBooking = RoomBooking::findOrFail($id);
            // first, delete the attachment
            Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_ATTACHMENTS), $roomBooking->attachment);
            // second, delete the invoice
            Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE), $roomBooking->invoice);

            // third, delete the room booking adjustments
            BookingAdjustment::where('booking_id', $roomBooking->id)->delete();

            // finally, delete the room booking record from db
            $roomBooking->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return 'success';
    }

    // room booking from admin panel
    public function bookedDates(Request $request)
    {
        $rule = [
            'room_category_id' => 'required',
            'dates' => 'required'
        ];

        $message = [
            'room_category_id.required' => __('Please select a room Category.'),
            'dates.required' => __('Please select Check In / Out Date.')
        ];

        $validator = Validator::make($request->all(), $rule, $message);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->getMessageBag()
            ]);
        }
        // get all the booked dates of the selected room
        $roomId = $request['room_category_id'];
        $dates = $request['dates'];
        $languageId = $request->filled('language_id') ? $request->language_id : null;

        // $bookedDates = $this->getBookedDatesOfRoom($roomId);
        // Session::put('bookedDates', $bookedDates);

        return response()->json([
            'success' => route('tenant.room_bookings.booking_form', [
                'room_category_id' => $roomId,
                'dates' => $dates,
                'language_id' => $languageId,
            ])

        ]);
    }

    public function getBookedDatesOfRoom($id, $bookingId = null)
    {
        $quantity = Room::query()->findOrFail($id)->quantity;
        $bookings = RoomBooking::query()->where('room_id', '=', $id)
            ->where('payment_status', '=', 1)
            ->select('arrival_date', 'departure_date')
            ->get();
        $bookedDates = [];
        foreach ($bookings as $booking) {
            // get all the dates between the booking arrival date & booking departure date
            $date_1 = $booking->arrival_date;
            $date_2 = $booking->departure_date;
            $allDates = $this->getAllDates($date_1, $date_2, 'Y-m-d');
            // loop through the list of dates, which we have found from the booking arrival date & booking departure date
            foreach ($allDates as $date) {
                $bookingCount = 0;
                // loop through all the bookings
                foreach ($bookings as $currentBooking) {
                    $bookingStartDate = Carbon::parse($currentBooking->arrival_date);
                    $bookingEndDate = Carbon::parse($currentBooking->departure_date);
                    $currentDate = Carbon::parse($date);
                    // check for each date, whether the date is present or not in any of the booking date range
                    if ($currentDate->betweenIncluded($bookingStartDate, $bookingEndDate)) {
                        $bookingCount++;
                    }
                }

                // if the number of booking of a specific date is same as the room quantity, then mark that date as unavailable
                if ($bookingCount >= $quantity && !in_array($date, $bookedDates)) {
                    array_push($bookedDates, $date);
                }
            }
        }

        if (is_null($bookingId)) {
            return $bookedDates;
        } else {
            $booking = RoomBooking::query()->findOrFail($bookingId);
            $arrivalDate = $booking->arrival_date;
            $departureDate = $booking->departure_date;
            // get all the dates between the booking arrival date & booking departure date
            $bookingAllDates = $this->getAllDates($arrivalDate, $departureDate, 'Y-m-d');

            // remove dates of this booking from 'bookedDates' array while editing a room booking
            foreach ($bookingAllDates as $date) {
                $key = array_search($date, $bookedDates);
                if ($key !== false) {
                    unset($bookedDates[$key]);
                }
            }
            return array_values($bookedDates);
        }
    }

    public function getAllDates($startDate, $endDate, $format)
    {
        $dates = [];
        // convert string to timestamps
        $currentTimestamps = strtotime($startDate);
        $endTimestamps = strtotime($endDate);
        // set an increment value
        $stepValue = '+1 day';
        // push all the timestamps to the 'dates' array by formatting those timestamps into date
        while ($currentTimestamps <= $endTimestamps) {
            $formattedDate = date($format, $currentTimestamps);
            array_push($dates, $formattedDate);
            $currentTimestamps = strtotime($stepValue, $currentTimestamps);
        }
        return $dates;
    }

    public function bookingForm(Request $request)
    {
        $information = [];
        $userId = Auth::guard('web')->user()->id;
        $language = $this->languageForRequest($userId, $request);

        $information['datesC'] = $request->dates;
        $information['language'] = $language;

        // Parse date range
        [$startDate, $endDate] = explode(' - ', $request->dates);
        $start = \Carbon\Carbon::parse($startDate);
        $end   = \Carbon\Carbon::parse($endDate)->subDay(); // inclusive end

        // Safety defaults
        $dateStr = $start->format('Y-m-d');

        $maxRoomsPerDay = 1;

        $roomCategory = Room::findOrFail($request->room_category_id);

        // Load booked room numbers by date
        $roomBookings = RoomBooking::where('room_category_id', $request->room_category_id)
            ->where('booking_status', '!=', 2)
            ->whereNotNull('reserved_dates_info')
            ->get(['reserved_dates_info']);

        $bookedRoomsByDate = [];
        foreach ($roomBookings as $booking) {
            $reserved = is_string($booking->reserved_dates_info)
                ? json_decode($booking->reserved_dates_info, true)
                : $booking->reserved_dates_info;

            if (!is_array($reserved)) continue;

            foreach ($reserved as $entry) {
                if (!is_array($entry) || !isset($entry['date'])) continue;

                $date = $entry['date'];
                foreach ($this->reservedRoomKeys($entry) as $roomKey) {
                    $bookedRoomsByDate[$date][] = $roomKey;
                }
            }
        }

        // All active rooms in this category
        $allRooms = $this->activeRoomNumbersForCategory($userId, $request->room_category_id, $language?->id);
        // Price calculation
        $roomPrices = Common::priceCalculation($roomCategory, $startDate, $endDate);
        // Build priceByDate map
        $reqDatePrices = $roomPrices['dailyDetails'] ?? [];
        $priceByDate = collect($reqDatePrices)->pluck('price', 'date')->toArray();

        // Build daily room status (dates)
        $dates = [];
        $loopStart = $start->copy();
        while ($loopStart->lte($end)) {
            $dateStr = $loopStart->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];
            $rentForDate = $priceByDate[$dateStr] ?? $roomCategory->regular_price;

            $rooms = $allRooms->map(function ($room) use ($bookedRoomKeys, $rentForDate) {
                return [
                    'id'          => $room->id,
                    'room_number' => $this->roomNumberContentName($room),
                    'status'      => $this->roomMatchesAnyKey($room, $bookedRoomKeys) ? 'booked' : 'available',
                    'rent'        => $rentForDate,
                ];
            })->values()->toArray();

            $dates[] = [
                'date'  => $dateStr,
                'rooms' => $rooms,
            ];

            $loopStart->addDay();
        }

        // Check if any date has insufficient rooms
        $insufficientDate = null;
        $availableCount = 0;

        $checkDate = $start->copy();
        while ($checkDate->lte($end)) {
            $dateStr = $checkDate->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];

            $availableCount = $allRooms->filter(function ($room) use ($bookedRoomKeys) {
                return !$this->roomMatchesAnyKey($room, $bookedRoomKeys);
            })->count();

            if ($availableCount < $maxRoomsPerDay) {
                $insufficientDate = $dateStr;
                break;
            }

            $checkDate->addDay();
        }

        // Suggested rooms available for ALL dates (intersection across each day)
        $interval = $start->diffInDays($end) + 1;

        $commonAvailable = null;
        $loopDate = $start->copy();
        while ($loopDate->lte($end)) {
            $dateStr = $loopDate->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];

            $availableToday = $allRooms
                ->filter(function ($room) use ($bookedRoomKeys) {
                    return !$this->roomMatchesAnyKey($room, $bookedRoomKeys);
                })
                ->map(function ($room) {
                    return $this->roomPrimaryKey($room);
                })
                ->toArray();

            $commonAvailable = is_null($commonAvailable)
                ? $availableToday
                : array_values(array_intersect($commonAvailable, $availableToday));

            $loopDate->addDay();
        }

        $commonAvailable = $commonAvailable ?? [];
        $commonAvailable = array_slice($commonAvailable, 0, $maxRoomsPerDay);

        $roomList = [];
        foreach ($commonAvailable as $roomKey) {
            $room = $this->findRoomByKey($allRooms, $roomKey);
            if (!$room) continue;
            $rentsRow = Room::where('id', $roomCategory->id)
                ->select('regular_price', 'weekend_price', 'seasonal_price')
                ->first();

            $rents = $rentsRow ? $rentsRow->toArray() : [
                'regular_price'  => 0,
                'weekend_price'  => 0,
                'seasonal_price' => 0,
            ];

            $roomList[] = [
                'room_number' => $this->roomNumberContentName($room),
                'room_id'     => $room->id,
                'rent'        => $rents,
                'days'        => (int) $interval,
            ];
        }


        // Assign information
        $information['interval']         = $interval;
        $information['dates']            = $dates;
        $information['roomList']           = $roomList;
        $information['totalRooms']       = $maxRoomsPerDay;
        $information['discount']         = 0.00;
        $information['insufficientDate'] = $insufficientDate;
        $information['dateStr']          = $start->format('Y-m-d');
        $information['availableCount']   = $availableCount;
        $information['totalRent']        = $roomPrices['totalPrice'] ?? 0;

        // Gateways
        $userId = Auth::guard('web')->user()->id;

        $information['onlineGateways'] = OnlineGateway::query()
            ->where('status', 1)
            ->where('user_id', $userId)
            ->select('name')
            ->get();

        $information['offlineGateways'] = OfflineGateway::query()
            ->where('status', 1)
            ->where('user_id', $userId)
            ->select('name')
            ->orderBy('serial_number', 'asc')
            ->get();
        $information['userId'] = $userId;

        return view('user.rooms.booking.booking-engine.form', $information);
    }


    public function totalRooms(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $language = $this->languageForRequest($userId, $request);

        // Optional booking ID (used to exclude a specific booking when editing)
        $bookingId = null;
        if ($request->bookingId) {
            $bookingId = $request->bookingId;
        }

        // Split the date range from request (format: 'Y-m-d - Y-m-d')
        [$startDate, $endDate] = explode(' - ', $request->dates);
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate)->subDay(); // exclude checkout day
        $interval = $start->diffInDays($end) + 1; // total number of booking days

        // Max number of rooms needed per day
        $maxRoomsPerDay = (int) $request->totalRooms;

        // Get room category and rent
        $roomCategory = Room::findOrFail($request->roomCategoryId);
        // Get basic settings like tax and currency format
        $userBs = BasicSetting::where('user_id', $userId)->select('base_currency_text', 'base_currency_symbol_position')->first();


        // Get all active rooms for the given category
        $allRooms = $this->activeRoomNumbersForCategory($userId, $request->roomCategoryId, $language?->id);

        // Step 1: Load all booked room numbers and group them by date
        $roomBookings = RoomBooking::where('room_category_id', $request->roomCategoryId)
            ->where('booking_status', '!=', 2)
            ->whereNotNull('reserved_dates_info')
            ->when($bookingId, function ($query) use ($bookingId) {
                $query->where('id', '!=', $bookingId); // exclude current booking if editing
            })
            ->get(['reserved_dates_info']);

        $bookedRoomsByDate = [];

        foreach ($roomBookings as $booking) {
            $reserved = is_string($booking->reserved_dates_info)
                ? json_decode($booking->reserved_dates_info, true)
                : $booking->reserved_dates_info;

            if (!is_array($reserved)) continue;

            foreach ($reserved as $entry) {
                if (!is_array($entry) || !isset($entry['date'])) continue;

                $date = $entry['date'];
                foreach ($this->reservedRoomKeys($entry) as $roomKey) {
                    $bookedRoomsByDate[$date][] = $roomKey;
                }
            }
        }

        $roomPrices = Common::priceCalculation($roomCategory, $startDate, $endDate);
        $reqDatePrices = $roomPrices['dailyDetails'] ?? [];
        $priceByDate = collect($reqDatePrices)->pluck('price', 'date')->toArray();

        // Step 2: Build daily room status for calendar view
        $dates = [];
        $tempStart = $start->copy();

        while ($tempStart->lte($end)) {
            $dateStr = $tempStart->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];
            $rentForDate = $priceByDate[$dateStr] ?? $roomCategory->regular_price;

            $rooms = $allRooms->map(function ($room) use ($bookedRoomKeys, $rentForDate) {
                return [
                    'id' => $room->id,
                    'room_number' => $this->roomNumberContentName($room),
                    'status' => $this->roomMatchesAnyKey($room, $bookedRoomKeys) ? 'booked' : 'available',
                    'rent' => $rentForDate,
                ];
            })->values()->toArray();

            $dates[] = [
                'date' => $dateStr,
                'rooms' => $rooms,
            ];

            $tempStart->addDay();
        }

        // Step 2b: Check for any date with insufficient available rooms
        $tempStart = $start->copy();
        $insufficientDate = null;

        while ($tempStart->lte($end)) {
            $dateStr = $tempStart->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];

            $availableCount = $allRooms->filter(function ($room) use ($bookedRoomKeys) {
                return !$this->roomMatchesAnyKey($room, $bookedRoomKeys);
            })->count();

            if ($availableCount < $maxRoomsPerDay) {
                $insufficientDate = $dateStr;
                break; // stop checking after first insufficient date
            }

            $tempStart->addDay();
        }

        // Step 3: Build a suggested list of rooms available across the full interval
        $roomSummary = [];
        $dailySummaryCounter = [];
        $loopDate = $start->copy();

        while ($loopDate->lte($end)) {
            $dateStr = $loopDate->format('Y-m-d');
            $bookedRoomKeys = $bookedRoomsByDate[$dateStr] ?? [];
            $rentForDate = (float) ($priceByDate[$dateStr] ?? $roomCategory->regular_price);

            foreach ($allRooms as $room) {
                $roomKey = $this->roomPrimaryKey($room);

                // Skip room if already assigned for enough days
                if (isset($roomSummary[$roomKey]) && $roomSummary[$roomKey]['days'] >= $interval) {
                    continue;
                }

                // Skip if already booked for this date
                if ($this->roomMatchesAnyKey($room, $bookedRoomKeys)) {
                    continue;
                }

                // Limit max number of rooms per day
                if (!isset($dailySummaryCounter[$dateStr])) {
                    $dailySummaryCounter[$dateStr] = 0;
                }

                if ($dailySummaryCounter[$dateStr] >= $maxRoomsPerDay) {
                    continue;
                }

                // Add room to the summary list
                if (!isset($roomSummary[$roomKey])) {
                    $roomSummary[$roomKey] = [
                        'room_number' => $this->roomNumberContentName($room),
                        'room_id' => $room->id,
                        'rent' => $rentForDate,
                        'days' => 0,
                        'total_rent' => 0,
                        'daily_rents' => [],
                    ];
                }

                $roomSummary[$roomKey]['days'] += 1;
                $roomSummary[$roomKey]['total_rent'] += $rentForDate;
                $roomSummary[$roomKey]['daily_rents'][$dateStr] = $rentForDate;
                $dailySummaryCounter[$dateStr] += 1;
            }

            $loopDate->addDay();
        }

        // Prepare final list for room suggestion view
        $roomList = array_values(array_map(function ($room) {
            $uniqueRents = array_values(array_unique(array_map(function ($rent) {
                return round((float) $rent, 2);
            }, $room['daily_rents'] ?? [])));

            $room['total_rent'] = round((float) ($room['total_rent'] ?? 0), 2);
            $room['is_variable_rent'] = count($uniqueRents) > 1;
            $room['rent'] = $room['is_variable_rent']
                ? null
                : ($uniqueRents[0] ?? round((float) ($room['rent'] ?? 0), 2));

            unset($room['daily_rents']);

            return $room;
        }, $roomSummary));
        $dates2[] = [
            'rooms' => $roomList,
        ];

        // Render the booking availability view with all data
        return view('user.rooms.booking.available-room', [
            'warning' => 'Updating the date will reset assigned rooms. Please reassign rooms for each date.',
            'dates' => $dates,
            'dates2' => $dates2,
            'totalRooms' => $request->totalRooms,
            'discount' => $request->discount,
            'userBs' => $userBs,
            'insufficientDate' => $insufficientDate,
            'dateStr' => $dateStr,
            'availableCount' => $availableCount,
        ])->render();
    }

    private function defaultLanguageForUser(int $userId): ?Language
    {
        return Language::where('user_id', $userId)
            ->where('is_default', 1)
            ->first()
            ?? Language::where('user_id', $userId)->orderBy('id')->first();
    }

    private function languageForRequest(int $userId, Request $request, $languages = null): ?Language
    {
        $languages = $languages ?? Language::where('user_id', $userId)
            ->orderBy('id')
            ->get();

        $defaultLanguage = $languages->firstWhere('is_default', 1) ?? $languages->first();

        if ($request->filled('language_id')) {
            return $languages->firstWhere('id', (int) $request->language_id) ?? $defaultLanguage;
        }

        if ($request->filled('language')) {
            return $languages->firstWhere('code', $request->language) ?? $defaultLanguage;
        }

        return $defaultLanguage;
    }

    private function activeRoomNumbersForCategory(int $userId, $roomCategoryId, ?int $languageId)
    {
        return RoomNumber::with(['contents' => function ($query) use ($userId, $languageId) {
            $query->where('user_id', $userId)
                ->when($languageId, function ($q, $languageId) {
                    return $q->where('language_id', $languageId);
                });
        }])
            ->where('user_id', $userId)
            ->where('room_category_id', $roomCategoryId)
            ->where('status', 1)
            ->get(['id', 'room_category_id']);
    }

    private function roomNumberContentName(RoomNumber $room): string
    {
        $name = $room->contents->first()?->name;

        return is_string($name) && trim($name) !== '' ? $name : 'N/A';
    }

    private function roomPrimaryKey(RoomNumber $room): string
    {
        return 'id:' . $room->id;
    }

    private function roomKeys(RoomNumber $room): array
    {
        $keys = [$this->roomPrimaryKey($room)];

        $roomName = $this->roomNumberContentName($room);
        if ($roomName !== 'N/A') {
            $keys[] = 'number:' . $roomName;
        }

        return $keys;
    }

    private function reservedRoomKeys(array $entry): array
    {
        $keys = [];

        if (!empty($entry['room_id'])) {
            $keys[] = 'id:' . (int) $entry['room_id'];
        }

        if (!empty($entry['room_number'])) {
            $keys[] = 'number:' . $entry['room_number'];
        }

        return array_values(array_unique($keys));
    }

    private function roomMatchesAnyKey(RoomNumber $room, array $keys): bool
    {
        return count(array_intersect($this->roomKeys($room), $keys)) > 0;
    }

    private function findRoomByKey($rooms, string $key): ?RoomNumber
    {
        [$type, $value] = array_pad(explode(':', $key, 2), 2, null);

        if ($type === 'id') {
            return $rooms->firstWhere('id', (int) $value);
        }

        if ($type === 'number') {
            return $rooms->first(function ($room) use ($value) {
                return $this->roomNumberContentName($room) === $value;
            });
        }

        return null;
    }

    private function syncBookingPricing(RoomBooking $booking): RoomBooking
    {
        if (!$this->shouldSyncBookingPricing($booking)) {
            if ((float) $booking->grand_total > 0) {
                BookingAdjustment::firstOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'user_id' => $booking->user_id,
                        'grand_total' => $booking->grand_total,
                        'amount' => 0,
                        'type' => 'initial',
                    ]
                );
            }

            return $booking;
        }

        $room = $booking->hotelRoom()->first();
        if (!$room) {
            return $booking;
        }

        $bs = BasicSetting::select(
            'room_tax_status',
            'room_tax',
            'room_fee_status',
            'room_fee'
        )->where('user_id', $booking->user_id)->first();

        $priceDetails = Common::priceCalculation($room, $booking->arrival_date, $booking->departure_date);
        $totalRooms = max(1, (int) ($booking->total_rooms ?: 1));
        $totalRent = round(((float) ($priceDetails['totalPrice'] ?? 0)) * $totalRooms, 2);
        $discount = round((float) $booking->discount, 2);
        $taxPercentage = round(
            (float) (((int) ($bs?->room_tax_status ?? 0) === 1) ? ($bs?->room_tax ?? 0) : 0),
            2
        );
        $fee = round(
            (float) (((int) ($bs?->room_fee_status ?? 0) === 1) ? ($bs?->room_fee ?? 0) : 0),
            2
        );

        $taxableBase = max(0, $totalRent - $discount);
        $taxAmount = round(($taxableBase * $taxPercentage) / 100, 2);
        $grandTotal = round($taxableBase + $taxAmount + $fee, 2);

        $advanceAmount = round((float) $booking->advance_amount, 2);
        $advancePaymentStatus = (int) $booking->advance_payment_status;
        if ($advancePaymentStatus === 0 && strtolower((string) $room->payment_system) === 'advance') {
            $configuredAdvanceAmount = round((float) $room->advance_amount, 2);
            if ($configuredAdvanceAmount > 0) {
                $advanceAmount = min($configuredAdvanceAmount, $grandTotal);
                $advancePaymentStatus = 1;
            }
        }

        $paidAmount = round((float) $booking->paid_amount, 2);
        $due = (int) $booking->payment_status === 3
            ? 0.00
            : round(max($grandTotal - $paidAmount, 0), 2);

        $booking->fill([
            'total_rent' => $totalRent,
            'tax_amount' => $taxAmount,
            'tax_percentage' => $taxPercentage,
            'fee' => $fee,
            'grand_total' => $grandTotal,
            'advance_amount' => $advanceAmount,
            'advance_payment_status' => $advancePaymentStatus,
            'due' => $due,
        ])->save();

        $booking->refresh();

        BookingAdjustment::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $booking->user_id,
                'grand_total' => $booking->grand_total,
                'amount' => 0,
                'type' => 'initial',
            ]
        );

        return $booking;
    }

    private function shouldSyncBookingPricing(RoomBooking $booking): bool
    {
        if ($this->hasAssignedRooms($booking) || (int) $booking->payment_status !== 0) {
            return false;
        }

        return (bool) $booking->hotelRoom()->first(['id', 'payment_system', 'advance_amount']);
    }

    private function hasAssignedRooms(RoomBooking $booking): bool
    {
        return !empty($this->decodeReservedDatesInfo($booking->reserved_dates_info));
    }

    private function decodeReservedDatesInfo($reservedDatesInfo): array
    {
        if (is_string($reservedDatesInfo)) {
            $decoded = json_decode($reservedDatesInfo, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($reservedDatesInfo)) {
            return $reservedDatesInfo;
        }

        return [];
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(Request $request)
    {
        $booking = RoomBooking::findOrFail($request->booking_id);

        if ($booking->reserved_dates_info == null) {
            session()->flash('not-assigned-booking-id', $booking->id);
            session()->flash('error', 'Room not assigned yet. Please assign room first to update the booking status.');
            return redirect()->back();
        }

        $booking->update(['booking_status' => $request->booking_status]);

        // Send WhatsApp message if the booking source is WhatsApp bot
        if ($booking->source == 'whatsapp_bot') {
            $wp = DB::table('whatsapps')
                ->where('id', $booking->wp_id)
                ->where('user_id', $booking->user_id)
                ->where('status', 1)
                ->select('id', 'wp_phone_number', 'wp_access_token')
                ->first();

            $replyText = ($booking->booking_status == 1)
                ? "Your booking (#{$booking->booking_number}) has been confirmed."
                : "Your booking (#{$booking->booking_number}) has been rejected.";

            $this->sendReply($wp, $booking->book_from_number, $replyText);
        }

        // delete previous invoice from local storage
        Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE), $booking->invoice);
        // then, generate an invoice in pdf format
        $invoice = MailService::generateBookingInvocie($booking);
        // update the invoice field information in database
        $booking->update(['invoice' => $invoice]);

        //Send mail to customer
        if ($booking->booking_status == 1) {
            $mail_type = 'room_booking_status_confirmed';
        } else {
            $mail_type = 'room_booking_status_rejected';
        }
        MailService::sendBookingMail($booking, $mail_type);

        session()->flash('success', 'Updated successfully!');
        return redirect()->back();
    }

    /**
     * update payment status of a booking
     */
    public function updatePaymentStatus(Request $request)
    {
        $booking = RoomBooking::findOrFail($request->booking_id);

        if ($booking->reserved_dates_info == null) {
            session()->flash('not-assigned-booking-id', $booking->id);
            session()->flash('error', 'Room not assigned yet. Please assign room first to update the payment status.');
            return redirect()->back();
        }

        //update payment status
        $booking->payment_status = $request->payment_status;
        $booking->save();

        // delete previous invoice from local storage
        Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE), $booking->invoice);
        // then, generate an invoice in pdf format
        $invoice = MailService::generateBookingInvocie($booking);
        // update the invoice field information in database
        $booking->update(['invoice' => $invoice]);

        // Send WhatsApp message if the booking source is WhatsApp bot
        if ($booking->source == 'whatsapp_bot') {
            $wp = DB::table('whatsapps')
                ->where('id', $booking->wp_id)
                ->where('user_id', $booking->user_id)
                ->where('status', 1)
                ->select('id', 'wp_phone_number', 'wp_access_token')
                ->first();

            $bs = DB::table('user_basic_settings')
                ->where('user_id', $booking->user_id)
                ->select('base_currency_symbol', 'base_currency_symbol_position')
                ->first();

            $symbol   = $bs?->base_currency_symbol ?? '';
            $position = $bs?->base_currency_symbol_position ?? 'left';

            $line = str_repeat('—', 22);

            $replyText  = "*Booking Confirmation*\n";
            $replyText .= "{$line}\n\n";

            $replyText .= "*Name:* {$booking->customer_name}\n";
            $replyText .= "*Email:* {$booking->customer_email}\n";
            $replyText .= "*Arrival Date:* {$booking->arrival_date}\n";
            $replyText .= "*Departure Date:* {$booking->departure_date}\n";

            if ($booking->due > 0) {
                $replyText .= "*Due:* " . currencyTextPrice($booking->due, $symbol, $position) . "\n";
            } else {
                $replyText .= "*Paid:* " . currencyTextPrice($booking->paid_amount, $symbol, $position) . "\n";
            }

            $replyText .= "\n*Reserved Dates*\n";

            $reserved = collect(json_decode($booking->reserved_dates_info, true) ?? [])
                ->sortBy('date')
                ->map(function ($item) {
                    $date = \Carbon\Carbon::parse($item['date'])->format('d M, Y');
                    $room = $item['room_number'] ?? $item['room_no'] ?? $item['room_numberno'] ?? 'N/A';
                    return "• {$date} — Room: {$room}";
                })
                ->values()
                ->all();

            if (!empty($reserved)) {
                $replyText .= implode("\n", $reserved) . "\n";
            } else {
                $replyText .= "• N/A\n";
            }

            $routelink = route('frontend.booking_status.view', ['id' => $booking->id]);
            $replyText .= "\n*For More Details:* {$routelink}\n";

            $this->sendReply($wp, $booking->book_from_number, $replyText);
        }

        //Send mail to customer
        if ($booking->payment_status == 1) {
            $mail_type = 'room_booking_payment_received';
        } else {
            $mail_type = 'room_booking_payment_cancelled';
        }
        MailService::sendBookingMail($booking, $mail_type);


        session()->flash('success', 'Updated successfully!');
        return redirect()->back();
    }

    /**
     * Make refund and update booking status to 'refunded'
     */
    public function makeRefund(Request $request)
    {
        $booking = RoomBooking::findOrFail($request->booking_id);
        $refundContext =  $request->input('refund_context', 'adjustment_refund');
        $refundType =  $request->input('refund_type', 'partial');

        // get refundable amount
        $refundableAmount = $booking->grand_total;
        if ($refundContext === 'booking_rejected') {
            $refundableAmount =  $booking->paid_amount;
        }

        $bookingAdjustment = BookingAdjustment::where('booking_id', $booking->id)->first();
        if ($refundContext !== 'booking_rejected' && $bookingAdjustment && $bookingAdjustment->type == 'refund') {
            $refundableAmount = $bookingAdjustment->amount;
        }

        if ($refundContext === 'booking_rejected') {
            $refundAmount = $this->calculateCancellationRefundAmount($booking, (float) $refundableAmount);
        } else {
            $rules = [
                'refund_type' => ['required', Rule::in(['full', 'partial'])],
            ];

            if ($refundType === 'partial') {
                // validation for partial refund amount
                $rules['refund_amount'] = 'required|numeric|min:1|max:' . $refundableAmount;
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response::json([
                    'errors' => $validator->getMessageBag()->toArray()
                ], 400);
            }

            if ($refundType == 'full') {
                $refundAmount = (float) $refundableAmount;
            } else {
                $refundAmount = (float) $request->refund_amount;
            }
        }

        //update booking adjustment information
        if ($bookingAdjustment && $bookingAdjustment->type == 'refund') {
            BookingAdjustmentService::updateForRefund($booking, $refundAmount);
        }

        //update booking information
        if ($refundContext === 'booking_rejected') {
            $booking->update(['booking_status' => 2]);
        } elseif (!$bookingAdjustment || $bookingAdjustment->type != 'refund') {
            $booking->update(['booking_status' => 2]);
        }

        //update refund information
        Refund::create([
            'booking_id'      => $booking->id,
            'user_id'         => $booking->user_id,
            'customer_name'   => $booking->customer_name,
            'customer_email'  => $booking->customer_email,
            'customer_phone'  => $booking->customer_phone,
            'paying_amount'   => $refundableAmount,
            'refund_amount'   => $refundAmount,
            'currency_symbol' => $booking->currency_symbol,
            'currency_symbol_position' => $booking->currency_symbol_position,
            'currency_text'   => $booking->currency_text,
            'currency_text_position'   => $booking->currency_text_position
        ]);

        session()->flash('success', 'Booking status updated successfully!');
        return "success";
    }

    private function calculateCancellationRefundAmount(RoomBooking $booking, float $refundableAmount): float
    {
        $settings = BasicSetting::select(
            'room_booking_cancellation',
            'cancellation_time_limit_hours',
            'cancellation_refund_percentage'
        )->where('user_id', $booking->user_id)->first();

        if (!$settings || $settings->room_booking_cancellation !== 'active') {
            return 0.00;
        }

        $refundPercentage = max(0, min(100, (float) $settings->cancellation_refund_percentage));
        if ($refundPercentage <= 0) {
            return 0.00;
        }

        $timeLimitHours = max(0, (int) $settings->cancellation_time_limit_hours);

        // Refund eligibility is calculated from booking creation datetime.
        $bookingDateTime = Carbon::parse($booking->created_at, config('app.timezone'));
        $hoursSinceBooking = $bookingDateTime->diffInHours(Carbon::now(config('app.timezone')));

        if ($hoursSinceBooking > $timeLimitHours) {
            return 0.00;
        }

        return round(($refundableAmount * $refundPercentage) / 100, 2);
    }

    /**
     * Refunds list
     */
    public function refunds()
    {
        $information['refunds'] = Refund::Where('user_id', Auth::guard('web')->user()->id)->orderBy('id', 'desc')
            ->get();
        $information['currencyInfo'] = MiscellaneousTrait::getCurrencyInfoUser();

        return view('user.rooms.booking.refunds', $information);
    }

    /**
     * Delete refund
     */
    public function deleteRefund(Request $request)
    {
        $refund = Refund::findOrFail($request->refund_id);

        BookingAdjustmentService::revertRefund($refund->refund_amount, $refund->booking_id);

        $refund->delete();

        session()->flash('success', 'Refund deleted successfully!');
        return redirect()->back();
    }


    public function updatePartialAmount(Request $request)
    {
        $roomBooking = RoomBooking::findOrFail($request->booking_id);

        if ($request->paying_amount <= 0) {
            session()->flash('warning', 'Paying amount must be greater than zero.');
            return redirect()->back();
        }
        if ($request->paying_amount > $roomBooking->due) {
            session()->flash('warning', 'Paying amount cannot exceed due amount.');
            return redirect()->back();
        }

        // Check payment status before updating due
        if (bccomp($roomBooking->due, $request->paying_amount, 2) === 0) {
            $roomBooking->payment_status = 1; // Paid
        } else {
            $roomBooking->payment_status = 3;
        }

        $roomBooking->due -= $request->paying_amount;
        $roomBooking->paid_amount += $request->paying_amount;

        $roomBooking->save();

        $invoice = MailService::generateBookingInvocie($roomBooking);

        $roomBooking->update(['invoice' => $invoice]);

        session()->flash('success', 'Payment status updated successfully!');

        return redirect()->back();
    }

    public function report(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $information['onlineGateways'] = OnlineGateway::query()
            ->where('status', 1)
            ->where('user_id', $userId)
            ->get();
        $information['offlineGateways'] = OfflineGateway::query()
            ->where('status', 1)
            ->where('user_id', $userId)
            ->orderBy('serial_number', 'asc')
            ->get();

        $information['isFilterApplied'] = $this->hasRoomReportFilters($request);

        if (!$information['isFilterApplied']) {
            Session::put('room_bookings', collect());
            $information['bookings'] = collect();
            return view('user.rooms.report', $information);
        }

        $records = $this->buildRoomReportQuery($request, $userId)
            ->select('*')
            ->orderByDesc('id');

        $bookings = $records->paginate(10)->withQueryString();
        $information['bookings'] = $this->manipulateCollection($bookings);

        // Keep filtered data in session for backward compatibility.
        Session::put('room_bookings', $this->manipulateCollection($records->get()));

        return view('user.rooms.report', $information);
    }

    public function manipulateCollection($bookings)
    {
        $userId = Auth::guard('web')->user()->id;

        $mapper = function ($booking) use ($userId) {
            $room = $booking->hotelRoom()->where('user_id', $userId)->first();
            $booking['roomTitle'] = $room
                ? $room->roomContent()->where('room_id', $room->id)->value('title')
                : 'N/A';

            $startDate = $booking->arrival_date ?? $booking->start_date;
            $endDate = $booking->departure_date ?? $booking->end_date;

            $booking['startDate'] = !empty($startDate)
                ? Carbon::parse($startDate)->format('M d, Y')
                : '-';
            $booking['endDate'] = !empty($endDate)
                ? Carbon::parse($endDate)->format('M d, Y')
                : '-';
            $booking['createdAt'] = !empty($booking->created_at)
                ? Carbon::parse($booking->created_at)->format('M d, Y')
                : '-';

            return $booking;
        };

        if ($bookings instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $bookings->setCollection($bookings->getCollection()->map($mapper));
            return $bookings;
        }

        return $bookings->map($mapper);
    }

    public function exportReport(Request $request)
    {
        if (!$this->hasRoomReportFilters($request)) {
            Session::flash('warning', __('Please search first before exporting.'));
            return redirect()->back();
        }

        $userId = Auth::guard('web')->user()->id;
        $roomBooking = $this->manipulateCollection(
            $this->buildRoomReportQuery($request, $userId)
                ->select('*')
                ->orderByDesc('id')
                ->get()
        );

        if (count($roomBooking) === 0) {
            Session::flash('error', __('There has no booking to export.'));
            return redirect()->back();
        }

        return Excel::download(new RoomBookingsExport($roomBooking), 'room-bookings.csv');
    }

    private function hasRoomReportFilters(Request $request): bool
    {
        $filterKeys = ['from', 'to', 'booking_no', 'payment_gateway', 'payment_status'];

        foreach ($filterKeys as $key) {
            if ($request->filled($key) && trim((string) $request->input($key)) !== '') {
                return true;
            }
        }

        return false;
    }

    private function buildRoomReportQuery(Request $request, int $userId): Builder
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->toDateString() : null;
        $to = $request->filled('to') ? Carbon::parse($request->to)->toDateString() : null;
        $paymentGateway = $request->filled('payment_gateway') ? trim((string) $request->payment_gateway) : null;
        $paymentStatus = $request->filled('payment_status') ? trim((string) $request->payment_status) : null;
        $bookingNo = $request->filled('booking_no') ? trim((string) $request->booking_no) : null;

        return RoomBooking::query()
            ->where('user_id', $userId)
            ->when($from && $to, function (Builder $query) use ($from, $to) {
                return $query
                    ->whereDate('arrival_date', '<=', $to)
                    ->whereDate('departure_date', '>=', $from);
            })
            ->when($from && !$to, function (Builder $query) use ($from) {
                return $query->whereDate('arrival_date', '>=', $from);
            })
            ->when(!$from && $to, function (Builder $query) use ($to) {
                return $query->whereDate('arrival_date', '<=', $to);
            })
            ->when($paymentGateway, function (Builder $query, $paymentGateway) {
                return $query->where('payment_method', '=', $paymentGateway);
            })
            ->when($paymentStatus === 'completed', function (Builder $query) {
                return $query->where('payment_status', 1);
            })
            ->when($paymentStatus === 'incompleted', function (Builder $query) {
                return $query->whereIn('payment_status', [0, 2, 3]);
            })
            ->when($bookingNo, function (Builder $query, $bookingNo) {
                return $query->where('booking_number', 'like', '%' . $bookingNo . '%');
            });
    }

    public function generateValidationRules(Request $request, $languages, $fields)
    {
        $rules = [];

        foreach ($languages as $language) {
            $prefix = $language->code . '_';

            if ($language->default == 1) {
                // If the language is default, all fields are required
                foreach ($fields as $field) {
                    $rules[$prefix . $field] = 'required';
                }
            } else {
                // For other languages, check if any field is filled
                $hasInput = false;
                foreach ($fields as $field) {
                    if ($request->input($prefix . $field)) {
                        $hasInput = true;
                        break;
                    }
                }

                // If any field has input, make all fields required for that language
                if ($hasInput) {
                    foreach ($fields as $field) {
                        $rules[$prefix . $field] = 'required';
                    }
                }
            }
        }

        return $rules;
    }

    public function taxFee()
    {
        $data['data'] = BasicSetting::select('room_tax_status', 'room_tax', 'room_fee_status', 'room_fee', 'base_currency_symbol')->where('user_id', Auth::guard('web')->user()->id)->first();

        return view('user.rooms.tex_fee', $data);
    }
    public function updateTaxFee(Request $request)
    {
        $rules = [
            'room_tax_status' => 'required',
            'room_tax' => 'required_if:room_tax_status,1',
            'room_fee_status' => 'required',
            'room_fee' => 'required_if:room_fee_status,1',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        BasicSetting::query()->updateOrInsert(
            ['user_id' => Auth::guard('web')->user()->id],
            $request->except(['_token', 'user_id'] + [
                'user_id' => Auth::guard('web')->user()->id,
            ])
        );
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }


    public function sendPaymentLink(Request $request)
    {
        $booking = RoomBooking::findOrFail($request->booking_id);
        WpTemplateMessageSend::sendPaymentLinkMessage($booking);

        session()->flash('success', 'Payment link sent successfully!');
        return 'success';
    }

    public function getLangwiseRoomCategory(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        return Room::join('user_room_category_contents', 'user_room_categories.id', '=', 'user_room_category_contents.room_id')
            ->where('user_room_category_contents.language_id', $request->language_id)
            ->where('user_room_categories.user_id', $userId)
            ->select('user_room_categories.*', 'user_room_category_contents.title', 'user_room_category_contents.slug')
            ->orderBy('user_room_categories.id', 'desc')
            ->get();
    }
}
