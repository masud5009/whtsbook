<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Package;
use App\Models\User\Role;
use App\Models\User\Room;
use App\Models\Membership;
use App\Models\User\Staff;
use App\Constants\Constant;
use App\Models\AiUsageToken;
use Illuminate\Http\Request;
use App\Models\BasicExtended;
use App\Http\Helpers\Uploader;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use App\Models\AiTokenRecharge;
use Illuminate\Validation\Rule;
use App\Http\Helpers\MegaMailer;
use App\Models\Language as AdminLang;
use App\Models\User\BasicSetting;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserPermission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\AiUsageTokenService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Helpers\UserPermissionHelper;
use App\Models\User\PaymentGateway as OnlineGateway;

class RegisterUserController extends Controller
{
    public $carbon_max_Date;
    public function __construct()
    {
        $this->carbon_max_Date = Carbon::create(9999, 12, 31, 23, 59, 59);
    }

    public function index(Request $request)
    {
        $term = $request->term;
        $users = User::when($term, function ($query, $term) {
            $query->where('username', 'like', '%' . $term . '%')->orWhere('email', 'like', '%' . $term . '%');
        })->orderBy('id', 'DESC')->paginate(10);

        $online = PaymentGateway::query()->where('status', 1)->get();
        $offline = OfflineGateway::where('status', 1)->get();
        $gateways = $online->merge($offline);
        $packages = Package::query()->where('status', '1')->get();

        return view('admin.register_user.index', compact('users', 'gateways', 'packages'));
    }

    public function view($id)
    {

        $user = User::findOrFail($id);
        $packages = Package::query()->where('status', '1')->get();
        $online = PaymentGateway::query()->where('status', 1)->get();
        $offline = OfflineGateway::where('status', 1)->get();
        $gateways = $online->merge($offline);
        return view('admin.register_user.details', compact('user', 'packages', 'gateways'));
    }

    public function updateUser(Request $request)
    {

        $user = User::where('id', $request->user_id)->first();;
        $rules = [
            'email' => ['email', Rule::unique('users', 'email')->ignore($user->id)]
        ];
        $messages = [
            'email.unique' => __('The email has already taken'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email =  $request->email;
        $user->company_name = $request->company_name;
        $user->phone = $request->phone;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->address = $request->address;
        $user->country = $request->country;
        $user->update();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function store(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = AdminLang::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = AdminLang::where('is_default', 1)->first();
        }

        $timezone = TimzeZoneService::getAdminTimeZone();
        $today = Carbon::now($timezone)->startOfDay();

        $bs = $currentLang->basic_setting;
        $be = $currentLang->basic_extended;

        $rules = [
            'username' => 'required|alpha_num|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'package_id' => 'required',
            'payment_gateway' => 'required',
            'online_status' => 'required',
            'password_confirmation' => 'required'
        ];
        $messages = [
            'package_id.required' => __('The package field is required'),
            'online_status.required' => __('The publicly hidden field is required')
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $user = User::where('username', $request['username']);
        if ($user->count() == 0) {
            $user = User::create([
                'email' => $request['email'],
                'username' => $request['username'],
                'password' => bcrypt($request['password']),
                'online_status' => $request["online_status"],
                'status' => 1,
                'email_verified' => 1,
                'company_name' => ucfirst($request['username'])
            ]);

            BasicSetting::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'smtp_status' => 1,
                'email_address' => $user->email,
                'website_title' => $bs->website_title,
                'address' =>  null,
                'base_currency_symbol_position' => $be->base_currency_symbol_position,
                'base_currency_symbol' => $be->base_currency_symbol ?? $be->base_currency_text,
                'base_currency_text' => $be->base_currency_text,
                'base_currency_text_position' => $be->base_currency_text_position,
                'base_currency_rate' => $be->base_currency_rate,
                'primary_color' => 'FEA116',
                'secondary_color' => '0F172B',
                'contact_number' => null,
                'reply_to' => $request['email'],
                'from_name' => 'Hotelia',
                'package_guest_checkout_status' => 1
            ]);

            $payment_keywords = [
                'flutterwave',
                'razorpay',
                'paytm',
                'paystack',
                'instamojo',
                'stripe',
                'paypal',
                'mollie',
                'mercadopago',
                'authorize.net'
            ];

            foreach ($payment_keywords as $value) {
                OnlineGateway::create([
                    'title' => null,
                    'user_id' => $user->id,
                    'details' => null,
                    'keyword' => $value,
                    'subtitle' => null,
                    'name' => ucfirst($value),
                    'type' => 'automatic',
                    'information' => null,
                    'status' => 0
                ]);
            }
        }

        if ($user) {

            $this->tenant_lanuage_related_table_create($user);

            $package = Package::find($request['package_id']);

            AiUsageTokenService::creditBaseTokensFromMembership(
                $user->id,
                (int)$package->total_ai_token
            );

            $be = BasicExtended::first();
            $bs = BasicSetting::select('website_title')->first();

            $transaction_id = UserPermissionHelper::uniqidReal(8);

            $startDate = $today->copy()->format('Y-m-d');

            if ($package->term === "monthly") {
                $endDate = $today->copy()->addMonth()->format('Y-m-d');
            } elseif ($package->term === "yearly") {
                $endDate = $today->copy()->addYear()->format('Y-m-d');
            } elseif ($package->term === "lifetime") {
                $endDate = $today->copy()->addYears(999)->format('Y-m-d');
            }

            $memb = Membership::create([
                'package_price' => $package->price,
                'price' => $package->price,
                'currency' => $be->base_currency_text ? $be->base_currency_text : "USD",
                'currency_symbol' => $be->base_currency_symbol ? $be->base_currency_symbol : $be->base_currency_text,
                'payment_method' => $request["payment_gateway"],
                'transaction_id' => $transaction_id ? $transaction_id : 0,
                'status' => 1,
                'is_trial' => 0,
                'trial_days' => 0,
                'receipt' => $request["receipt_name"] ? $request["receipt_name"] : null,
                'transaction_details' => null,
                'settings' => json_encode($be),
                'package_id' => $request['package_id'],
                'user_id' => $user->id,
                'start_date' => Carbon::parse($startDate, $timezone),
                'expire_date' => Carbon::parse($endDate, $timezone),
            ]);

            $package = Package::findOrFail($request['package_id']);

            $features = json_decode($package->features, true);
            $features[] = "Contact";

            UserPermission::create([
                'package_id' => $request['package_id'],
                'user_id' => $user->id,
                'permissions' => json_encode($features)
            ]);

            $requestData = [
                'start_date' => $startDate,
                'expire_date' => $endDate,
                'payment_method' => $request['payment_gateway']
            ];

            $file_name = $this->makeInvoice(
                $requestData,
                "membership",
                $user,
                null,
                $package->price,
                $request['payment_gateway'],
                null,
                $be->base_currency_symbol_position,
                $be->base_currency_symbol,
                $be->base_currency_text,
                $transaction_id,
                $package->title,
                $memb
            );

            $mailer = new MegaMailer();

            $startDate = Carbon::parse($startDate, $timezone);
            $endDate = Carbon::parse($endDate, $timezone);

            $data = [
                'toMail' => $user->email,
                'toName' => $user->fname,
                'username' => $user->username,
                'package_title' => $package->title,
                'package_price' => (
                    $be->base_currency_text_position == 'left'
                    ? $be->base_currency_text . ' '
                    : ''
                ) . $package->price . (
                    $be->base_currency_text_position == 'right'
                    ? ' ' . $be->base_currency_text
                    : ''
                ),
                'activation_date' => $startDate->toFormattedDateString(),
                'expire_date' => $endDate->toFormattedDateString(),
                'membership_invoice' => $file_name,
                'website_title' => $bs->website_title,
                'templateType' => 'registration_with_premium_package',
                'type' => 'registrationWithPremiumPackage'
            ];

            $mailer->mailFromAdmin($data);
        }

        Session::flash('success', __('Created Successfully'));

        return "success";
    }

    public function userban(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->update([
            'status' => $request->status,
        ]);
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function userPublicly(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->update([
            'online_status' => $request->online_status,
        ]);
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function emailStatus(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'email_verified' => $request->email_verified,
        ]);
        Session::flash('success', __('Email status updated for') . ' ' . $user->username);
        return back();
    }

    public function userFeatured(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $user->featured = $request->featured;
        $user->save();
        Session::flash('success', __('Updated Successfully'));
        return back();
    }

    public function changePass($id)
    {
        $data['user'] = User::findOrFail($id);
        return view('admin.register_user.password', $data);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required',
            'confirm_password' => 'required',
        ]);

        $user = User::findOrFail($request->user_id);
        if ($request->new_password == $request->confirm_password) {
            $input['password'] = Hash::make($request->new_password);
        } else {
            return back()->with('warning', __('Confirm password does not match'));
        }
        $user->update($input);
        Session::flash('success', __('Updated Successfully'));
        return 'success';
    }

    public function delete(Request $request)
    {
        $this->delete_user($request->user_id);
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $this->delete_user($id);
        }
        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }

    public function delete_user($id)
    {
        $user = User::query()->findOrFail($id);
        $bss = BasicSetting::query()->where('user_id', $user->id)->first();

        /**
         * delete room,roomContent,booking
         */
        $userRooms = Room::with('roomContent')
            ->where('user_id', $user->id)
            ->get();

        if ($userRooms->count() > 0) {
            foreach ($userRooms as $userRoom) {

                if ($userRoom->roomContent->count() > 0) {
                    foreach ($userRoom->roomContent as $room_content) {
                        $room_content->delete();
                    }
                }

                Uploader::remove(public_path(Constant::WEBSITE_ROOM_IMAGE), $userRoom->featured_img);

                foreach (json_decode($userRoom->slider_imgs, true) as $slider) {
                    Uploader::remove(public_path(Constant::WEBSITE_ROOM_SLIDER_IMAGE), $slider);
                }

                $userRoom->delete();
            }
        }

        /**
         * delete staff, role permissions
         */
        Staff::where('user_id', $user->id)->delete();
        Role::where('user_id', $user->id)->delete();

        /**
         * delete room and contents
         */
        DB::table('user_rooms')->where('user_id', $user->id)->delete();
        DB::table('user_room_contents')->where('user_id', $user->id)->delete();
        DB::table('auto_res_messages')->where('user_id', $user->id)->delete();


        /**
         * delete room booking
         */

        if ($user->bookHotelRoom->count() > 0) {
            foreach ($user->bookHotelRoom as $room_booking) {
                Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_ATTACHMENTS), $room_booking->attachment);
                Uploader::remove(public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE), $room_booking->invoice);
                $room_booking->delete();
            }
        }

        /**
         * delete  RoomAmenity
         */

        $roomAmenities = $user->room_amenities()->get();
        if ($roomAmenities->count() > 0) {
            foreach ($roomAmenities as $amenity) {
                $amenity->delete();
            }
        }


        /**
         * delete  RoomCoupon
         */
        $roomCoupons = $user->roomBookingCoupons()->get();
        if ($roomCoupons->count() > 0) {
            foreach ($roomCoupons as $coupon) {
                $coupon->delete();
            }
        }
        /**
         * delete room review
         */

        $roomReviews = $user->room_reviews()->get();
        if ($roomReviews->count() > 0) {
            foreach ($roomReviews as $roomReview) {
                $roomReview->delete();
            }
        }

        /**
         * delete 'memberships' info
         */
        $memberships = $user->memberships()->get();
        if ($memberships->count() > 0) {
            foreach ($memberships as $membership) {

                if (!is_null($membership->receipt)) {
                    @unlink(public_path('assets/front/img/membership/receipt/' . $membership->receipt));
                }
                $membership->delete();
            }
        }

        /**
         * delete 'QR Codes' info
         */

        if ($user->qr_codes()->count() > 0) {

            $qr_codes = $user->qr_codes()->get();
            foreach ($qr_codes as $qr) {
                Uploader::remove(public_path(Constant::WEBSITE_QRCODE_IMAGE), $qr->image);
                $qr->delete();
            }
        }

        /**
         * delete 'languages' info
         */
        if ($user->languages()->count() > 0) {
            $user->languages()->delete();
        }

        /**
         * delete tickets
         */
        $userTickets = $user->tickets()->with('messages')->get();
        if ($userTickets->count() > 0) {
            foreach ($userTickets as $userTicket) {
                foreach ($userTicket->messages as $mess) {
                    $mess->delete();
                }
                $userTicket->delete();
            }
        }


        /**
         * delete UserPermissions
         */
        $userPermissions = $user->permissions()->get();
        if ($userPermissions->count() > 0) {
            foreach ($userPermissions as $permission) {
                $permission->delete();
            }
        }


        /**
         * delete 'online gateways' info
         */
        $online_gateways = $user->online_gateways()->get();
        foreach ($online_gateways as $online_gateway) {
            if (!empty($online_gateway)) {
                $online_gateway->delete();
            }
        }

        /**
         * delete 'offline gateways' info
         */
        $offline_gateways = $user->offline_gateways()->get();
        foreach ($offline_gateways as $offline_gateway) {
            if (!empty($offline_gateway)) {
                $offline_gateway->delete();
            }
        }

        /**
         * delete 'mail templates' info
         */
        $mail_templates = $user->mail_templates()->get();
        if (!empty($mail_templates)) {
            foreach ($mail_templates as $mt) {
                if (!empty($mt)) {
                    $mt->delete();
                }
            }
        }


        /**
         * delete 'basic settings' info
         */
        $bs = $user->basic_setting()->first();
        if (!empty($bs)) {
            Uploader::remove(public_path(Constant::WEBSITE_FAVICON), $bs->favicon, $bss, $user->id);
            Uploader::remove(public_path(Constant::WEBSITE_LOGO), $bs->logo, $bss, $user->id);
            Uploader::remove(public_path(Constant::WEBSITE_MAINTENANCE_IMAGE), $bs->maintenance_img, $bss, $user->id);
            Uploader::remove(public_path(Constant::WEBSITE_BREADCRUMB), $bs->breadcrumb, $bss, $user->id);
            Uploader::remove(public_path(Constant::WEBSITE_FOOTER_LOGO), $bs->footer_logo, $bss, $user->id);
            Uploader::remove(public_path(Constant::WEBSITE_QRCODE_IMAGE), $bs->qr_image, $bss, $user->id);
            Uploader::remove(public_path(Constant::WEBSITE_QRCODE_IMAGE), $bs->qr_inserted_image, $bss, $user->id);
            $bs->delete();
        }

        //user profile image
        if (!is_null($user->photo)) {
            Uploader::remove(public_path(Constant::WEBSITE_TENANT_IMAGE), $user->photo);
            @unlink(public_path('assets/tenant/img/users/' . $user->photo));
        }

        $user->aiUsage()->delete();

        $user->delete();
        return;
    }

    public function removeCurrPackage(Request $request)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();
        $today = Carbon::now($timezone)->startOfDay();

        $userId = $request->user_id;
        $user = User::findOrFail($userId);

        $currMembership = UserPermissionHelper::currMembOrPending($userId);
        $currPackage = Package::select('title')->findOrFail($currMembership->package_id);

        $nextMembership = UserPermissionHelper::nextMembership($userId);

        $be = BasicExtended::first();
        $bs = BasicSetting::select('website_title')->first();

        // just expire the current package
        $currMembership->expire_date = $today->copy()->subDay();

        $currMembership->modified = 1;

        if ($currMembership->status == 0) {
            $currMembership->status = 2;
        }

        $currMembership->save();

        // if next package exists
        if (!empty($nextMembership)) {

            $nextPackage = Package::find($nextMembership->package_id);

            $nextMembership->start_date = $today->copy();

            if ($nextPackage->term == 'monthly') {

                $nextMembership->expire_date = $today->copy()->addMonth();
            } elseif ($nextPackage->term == 'yearly') {

                $nextMembership->expire_date = $today->copy()->addYear();
            } elseif ($nextPackage->term == 'lifetime') {

                $nextMembership->expire_date = $this->carbon_max_Date;
            }

            $nextMembership->save();

            $features = json_decode($nextPackage->features, true);
            $features[] = "Contact";

            UserPermission::where('user_id', $user->id)->update([
                'package_id' => $nextPackage->id,
                'user_id' => $user->id,
                'permissions' => json_encode($features)
            ]);
        }

        $this->sendMail(
            NULL,
            NULL,
            $request->payment_method,
            $user,
            $bs,
            $be,
            'admin_removed_current_package',
            NULL,
            $currPackage->title
        );

        Session::flash('success', __('Deleted Successfully'));

        return back();
    }


    public function sendMail($memb, $package, $paymentMethod, $user, $bs, $be, $mailType, $replacedPackage = NULL, $removedPackage = NULL)
    {
        if ($mailType != 'admin_removed_current_package' && $mailType != 'admin_removed_next_package') {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $activation = $memb->start_date;
            $expire = $memb->expire_date;
            $info['start_date'] = $activation->toFormattedDateString();
            $info['expire_date'] = $expire->toFormattedDateString();
            $info['payment_method'] = $paymentMethod;
            $file_name = $this->makeInvoice($info, "membership", $user, NULL, $package->price, "Stripe", $user->phone, $be->base_currency_symbol_position, $be->base_currency_symbol, $be->base_currency_text, $transaction_id, $package->title, $memb);
        }

        $mailer = new MegaMailer();
        $data = [
            'toMail' => $user->email,
            'toName' => $user->fname,
            'username' => $user->username,
            'website_title' => $bs->website_title,
            'templateType' => $mailType
        ];

        if ($mailType != 'admin_removed_current_package' && $mailType != 'admin_removed_next_package') {
            $data['package_title'] = $package->title;
            $data['package_price'] = ($be->base_currency_text_position == 'left' ? $be->base_currency_text . ' ' : '') . $package->price . ($be->base_currency_text_position == 'right' ? ' ' . $be->base_currency_text : '');
            $data['activation_date'] = $activation->toFormattedDateString();
            $data['expire_date'] = Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString();
            $data['membership_invoice'] = $file_name;
        }
        if ($mailType != 'admin_removed_current_package' || $mailType != 'admin_removed_next_package') {
            $data['removed_package_title'] = $removedPackage;
        }

        if (!empty($replacedPackage)) {
            $data['replaced_package'] = $replacedPackage;
        }
        $mailer->mailFromAdmin($data);
    }

    public function changeCurrPackage(Request $request)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();
        $today = Carbon::now($timezone)->startOfDay();

        $userId = $request->user_id;
        $user = User::findOrFail($userId);

        $currMembership = UserPermissionHelper::currMembOrPending($userId);
        $nextMembership = UserPermissionHelper::nextMembership($userId);

        $be = BasicExtended::first();
        $bs = BasicSetting::select('website_title')->first();

        $selectedPackage = Package::find($request->package_id);

        if (!empty($nextMembership) && $selectedPackage->term == 'lifetime') {
            Session::flash('membership_warning', __('To add a Lifetime package as Current Package, You have to remove the next package'));
            return back();
        }

        // expire the current package
        $currMembership->expire_date = $today->copy()->subDay();
        $currMembership->modified = 1;

        if ($currMembership->status == 0) {
            $currMembership->status = 2;
        }

        $currMembership->save();

        // calculate expire date for selected package
        if ($selectedPackage->term == 'monthly') {
            $exDate = $today->copy()->addMonth();
        } elseif ($selectedPackage->term == 'yearly') {
            $exDate = $today->copy()->addYear();
        } elseif ($selectedPackage->term == 'lifetime') {
            $exDate = $today->copy()->addYears(999);
        }

        // store a new membership for selected package
        $selectedMemb = Membership::create([
            'price' => $selectedPackage->price,
            'currency' => $be->base_currency_text,
            'currency_symbol' => $be->base_currency_symbol,
            'payment_method' => $request->payment_method,
            'transaction_id' => uniqid(),
            'status' => 1,
            'receipt' => NULL,
            'transaction_details' => NULL,
            'settings' => json_encode($be),
            'package_id' => $selectedPackage->id,
            'user_id' => $userId,
            'start_date' => $today->copy(),
            'expire_date' => $exDate->copy(),
            'is_trial' => 0,
            'trial_days' => 0,
        ]);

        $features = json_decode($selectedPackage->features, true);
        $features[] = "Contact";

        UserPermission::where('user_id', $user->id)->update([
            'package_id' => $request['package_id'],
            'user_id' => $user->id,
            'permissions' => json_encode($features)
        ]);

        if (!empty($nextMembership) && $selectedPackage->term != 'lifetime') {
            $nextPackage = Package::find($nextMembership->package_id);

            $nextStartDate = $exDate->copy()->addDay();

            $nextMembership->start_date = $nextStartDate->copy();

            if ($nextPackage->term == 'monthly') {
                $nextMembership->expire_date = $nextStartDate->copy()->addMonth();
            } elseif ($nextPackage->term == 'yearly') {
                $nextMembership->expire_date = $nextStartDate->copy()->addYear();
            } else {
                $nextMembership->expire_date = $this->carbon_max_Date;
            }

            $nextMembership->save();
        }

        $currentPackage = Package::select('title')->findOrFail($currMembership->package_id);

        $this->sendMail(
            $selectedMemb,
            $selectedPackage,
            $request->payment_method,
            $user,
            $bs,
            $be,
            'admin_changed_current_package',
            $currentPackage->title
        );

        Session::flash('success', __('Changed Successfully'));

        return back();
    }

    public function addCurrPackage(Request $request)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();
        $today = Carbon::now($timezone)->startOfDay();

        $userId = $request->user_id;
        $user = User::findOrFail($userId);

        $be = BasicExtended::first();
        $bs = BasicSetting::select('website_title')->first();

        $selectedPackage = Package::find($request->package_id);

        // calculate expire date for selected package
        if ($selectedPackage->term == 'monthly') {
            $exDate = $today->copy()->addMonth();
        } elseif ($selectedPackage->term == 'yearly') {
            $exDate = $today->copy()->addYear();
        } elseif ($selectedPackage->term == 'lifetime') {
            $exDate = $today->copy()->addYears(999);
        }

        // store a new membership for selected package
        $selectedMemb = Membership::create([
            'price' => $selectedPackage->price,
            'currency' => $be->base_currency_text,
            'currency_symbol' => $be->base_currency_symbol,
            'payment_method' => $request->payment_method,
            'transaction_id' => uniqid(),
            'status' => 1,
            'receipt' => NULL,
            'transaction_details' => NULL,
            'settings' => json_encode($be),
            'package_id' => $selectedPackage->id,
            'user_id' => $userId,
            'start_date' => $today->copy(),
            'expire_date' => $exDate->copy(),
            'is_trial' => 0,
            'trial_days' => 0,
        ]);

        $features = json_decode($selectedPackage->features, true);
        $features[] = "Contact";

        UserPermission::where('user_id', $user->id)->update([
            'package_id' => $request['package_id'],
            'user_id' => $user->id,
            'permissions' => json_encode($features)
        ]);

        $this->sendMail(
            $selectedMemb,
            $selectedPackage,
            $request->payment_method,
            $user,
            $bs,
            $be,
            'admin_added_current_package'
        );

        Session::flash('success', __('Added Successfully'));

        return back();
    }

    public function removeNextPackage(Request $request)
    {
        $userId = $request->user_id;
        $user = User::findOrFail($userId);
        $be = BasicExtended::first();
        $bs = BasicSetting::select('website_title')->first();
        $nextMembership = UserPermissionHelper::nextMembership($userId);
        // set the start_date to unlimited
        $nextMembership->start_date = Carbon::parse($this->carbon_max_Date->format('d-m-Y'));
        $nextMembership->modified = 1;
        $nextMembership->save();
        $nextPackage = Package::select('title')->findOrFail($nextMembership->package_id);
        $this->sendMail(NULL, NULL, $request->payment_method, $user, $bs, $be, 'admin_removed_next_package', NULL, $nextPackage->title);

        Session::flash('success', __('Removed Successfully'));
        return back();
    }

    public function changeNextPackage(Request $request)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();

        $userId = $request->user_id;
        $user = User::findOrFail($userId);

        $bs = BasicSetting::select('website_title')->first();
        $be = BasicExtended::first();

        $nextMembership = UserPermissionHelper::nextMembership($userId);
        $nextPackage = Package::find($nextMembership->package_id);
        $selectedPackage = Package::find($request->package_id);

        $prevStartDate = Carbon::parse($nextMembership->start_date, $timezone)->startOfDay();

        // set the start_date to unlimited
        $nextMembership->start_date = Carbon::parse($this->carbon_max_Date->format('Y-m-d'), $timezone);
        $nextMembership->modified = 1;
        $nextMembership->save();

        // calculate expire date for selected package
        if ($selectedPackage->term == 'monthly') {
            $exDate = $prevStartDate->copy()->addMonth();
        } elseif ($selectedPackage->term == 'yearly') {
            $exDate = $prevStartDate->copy()->addYear();
        } elseif ($selectedPackage->term == 'lifetime') {
            $exDate = Carbon::parse($this->carbon_max_Date->format('Y-m-d'), $timezone);
        }

        // store a new membership for selected package
        $selectedMemb = Membership::create([
            'price' => $selectedPackage->price,
            'currency' => $be->base_currency_text,
            'currency_symbol' => $be->base_currency_symbol,
            'payment_method' => $request->payment_method,
            'transaction_id' => uniqid(),
            'status' => 1,
            'receipt' => NULL,
            'transaction_details' => NULL,
            'settings' => json_encode($be),
            'package_id' => $selectedPackage->id,
            'user_id' => $userId,
            'start_date' => $prevStartDate->copy(),
            'expire_date' => $exDate->copy(),
            'is_trial' => 0,
            'trial_days' => 0,
        ]);

        $this->sendMail(
            $selectedMemb,
            $selectedPackage,
            $request->payment_method,
            $user,
            $bs,
            $be,
            'admin_changed_next_package',
            $nextPackage->title
        );

        Session::flash('success', __('Changed Successfully'));

        return back();
    }

    public function addNextPackage(Request $request)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();

        $userId = $request->user_id;

        $hasPendingMemb = UserPermissionHelper::hasPendingMembership($userId);
        if ($hasPendingMemb) {
            Session::flash('membership_warning', __('This user already has a Pending Package. Please take an action (change / remove / approve / reject) for that package first.'));
            return back();
        }

        $currMembership = UserPermissionHelper::userPackage($userId);
        $currPackage = Package::find($currMembership->package_id);

        $be = BasicExtended::first();
        $user = User::findOrFail($userId);
        $bs = BasicSetting::select('website_title')->first();

        $selectedPackage = Package::find($request->package_id);

        if ($currMembership->is_trial == 1) {
            Session::flash('membership_warning', __('If your current package is trial package, then you have to change / remove the current package first.'));
            return back();
        }

        if ($currPackage->term != 'lifetime') {
            $nextStartDate = Carbon::parse($currMembership->expire_date, $timezone)->startOfDay()->addDay();

            if ($selectedPackage->term == 'monthly') {
                $exDate = $nextStartDate->copy()->addMonth();
            } elseif ($selectedPackage->term == 'yearly') {
                $exDate = $nextStartDate->copy()->addYear();
            } elseif ($selectedPackage->term == 'lifetime') {
                $exDate = Carbon::parse($this->carbon_max_Date->format('Y-m-d'), $timezone);
            }

            $selectedMemb = Membership::create([
                'price' => $selectedPackage->price,
                'currency' => $be->base_currency_text,
                'currency_symbol' => $be->base_currency_symbol,
                'payment_method' => $request->payment_method,
                'transaction_id' => uniqid(),
                'status' => 1,
                'receipt' => NULL,
                'transaction_details' => NULL,
                'settings' => json_encode($be),
                'package_id' => $selectedPackage->id,
                'user_id' => $userId,
                'start_date' => $nextStartDate->copy(),
                'expire_date' => $exDate->copy(),
                'is_trial' => 0,
                'trial_days' => 0,
            ]);

            $this->sendMail(
                $selectedMemb,
                $selectedPackage,
                $request->payment_method,
                $user,
                $bs,
                $be,
                'admin_added_next_package'
            );
        } else {
            Session::flash('membership_warning', __('If your current package is lifetime package, then you have to change / remove the current package first.'));
            return back();
        }

        Session::flash('success', __('Added Successfully'));

        return back();
    }

    public function secretLogin(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        if ($user) {
            if (Auth::guard('web')->login($user)) {
                return redirect()->route('user-dashboard')
                    ->withSuccess(__('You have Successfully loggedin'));
            }
        }
        return redirect("login")->withSuccess(__('Oppes! You have entered invalid credentials'));
    }
    public function tenant_lanuage_related_table_create($user)
    {
        $adminLangs = AdminLang::get();
        $langCount = User\Language::where('user_id', $user->id)->where('is_default', 1)->count();
        $customerLangKeywords = file_get_contents(resource_path('lang/customer-lang.json'));
        if ($langCount == 0) {
            foreach ($adminLangs as $lang) {
                User\Language::create([
                    'name' => $lang->name,
                    'code' => $lang->code,
                    'is_default' => $lang->is_default,
                    'dashboard_default' => $lang->dashboard_default,
                    'rtl' => $lang->rtl,
                    'user_id' => $user->id,
                    'keywords' => $customerLangKeywords
                ]);
            }
        }
        return;
    }


    public function topupAICredits($id)
    {
        $aiUsage = AiUsageToken::where('user_id', $id)->first();

        $totalUsed = (int) ($aiUsage->total_tokens ?? 0);
        $totalUsable = (int) ($aiUsage->total_usable_tokens ?? 0);
        $data['creditLeft'] = max($totalUsable - $totalUsed, 0);

        $data['aiUsage'] = $aiUsage;

        $user = User::findOrFail($id);
        $data['topups'] = AiTokenRecharge::where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->paginate(10);
        return view('admin.ai-credit.user-topups', $data);
    }
}
