<?php

namespace App\Http\Controllers\Front;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Package;
use App\Models\Language;
use App\Models\Membership;
use App\Models\AiUsageToken;
use Illuminate\Http\Request;
use App\Http\Helpers\MegaMailer;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\DB;
use App\Models\User\UserPermission;
use App\Http\Controllers\Controller;
use App\Models\Language as AdminLaguage;
use Illuminate\Support\Facades\Session;
use App\Services\Payment\PaymentHandler;
use App\Services\Payment\PaymentService;
use App\Http\Helpers\UserPermissionHelper;
use App\Http\Requests\Checkout\CheckoutRequest;

class CheckoutController extends Controller
{
    public function checkout(CheckoutRequest $request, PaymentService $paymentService)
    {
        $coupon = Coupon::where('code', Session::get('coupon'))->first();
        if (!empty($coupon)) {
            $coupon_count = $coupon->total_uses;
            if ($coupon->maximum_uses_limit != 999999) {
                if ($coupon_count == $coupon->maximum_uses_limit) {
                    Session::forget('coupon');
                    session()->flash('warning', __('This coupon reached maximum limit'));
                    return redirect()->back();
                }
            }
        }

        $amount = $request->price;
        $bs = DB::table('basic_extendeds')->select('base_currency_text', 'base_currency_rate')->first();

        //for free subscription
        if ($request->package_type == "trial" || $request->price == 0) {
            $transaction_details = $request->package_type == "trial" ? "Trial" : "Free";
            $metadata = [
                'package_id' => $request->package_id,
                'amount' => 0,
                'password' => $request->password,
                'transaction_details' => $transaction_details,
                'transaction_id' => UserPermissionHelper::uniqidReal(8),
                'phone' => $request->phone,
                'username' => $request->username,
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => $request->password,
                'company_name' => $request->company_name,
                'mode' => 'online',
                'status' => 1,
                'address' => $request->address,
                'country' => $request->country ?? null,
                'city' => $request->city ?? null,
                'district' => $request->district ?? null,
                'trial_days' => $request->trial_days,
                'package_type' => $request->package_type,
                'start_date' => $request->start_date,
                'expire_date' => $request->expire_date,
                'payment_method' => '-',

            ];
            PaymentHandler::membershipBuy($metadata);

            return response()->json([
                'status' => 'success',
                'url' => route('trial-success.success.page'),
                'action' => 'redirect',
            ]);
        }

        //For paid subscription
        $gateway = strtolower(trim((string) $request->payment_method));
        $response = PaymentHandler::checkGateway($gateway, $amount, $bs);

        //currency check
        if ($response['status'] === 'error') {
            return response()->json([
                'status' => 'currency-error',
                'message' => $response['message'],
            ]);
        }

        $successUrl = match ($gateway) {
            'razorpay'  => route('membership.razorpay.success'),
            'paytm'     => route('membership.paytm.success'),
            'paytabs'   => route('membership.paytabs.success'),
            'iyzico'    => route('membership.iyzico.success'),
            default     => route('membership.success') . '?gateway=' . $response['gateway'],
        };

        $data = [
            'payment_for' => 'Membership Buy',
            'amount' => $amount,
            'formatted_amount' => $response['amount'],
            'currency' => $response['currency'] ?? $bs->base_currency_text,
            'customer_name' => $request->first_name . ' ' . $request->last_name,
            'customer_phone' => $request->phone,
            'customer_email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => $request->password,
            'username' => $request->username,
            'company_name' => $request->company_name,

            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'package_id' => $request->package_id,
            'status' => 1,
            'gateway' => $gateway,
            'is_admin' => 1, // 0 = user, 1 = admin (use for get paymentgateway info)
            //success and cancel url
            'success_url' => $successUrl,
            'cancel_url' => route('membership.cancel') . '?gateway=' . $gateway,
            //stripe
            'stripeToken' => $request->stripeToken ?? null,
            //authorize
            'opaqueDataValue' => $request->opaqueDataValue ?? null,
            'opaqueDataDescriptor' => $request->opaqueDataDescriptor ?? null,
            //offline gateway
            'gatewayId' => $response['gatewayType'] === 'offline' ? $request->payment_method : null,
            'gateway_type' => $response['gatewayType'],
            //iyzico
            'identity_number' => $request->identity_number ?? null,
            'address' => $request->address ?? null,
            'zip_code' => $request->zip_code ?? null,
            'country' => $request->country ?? null,
            'city' => $request->city ?? null,
            'district' => $request->district ?? null,
            'mode' => 'online',
            'package_type' => $request->package_type,
        ];
        $link = $paymentService->pay($data);

        return normalizePaymentResponse($request, $link);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($request, $transaction_id, $transaction_details, $amount, $be, $password)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();


        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $bs = $currentLang->basic_setting;
        $token = md5(time() . $request['username'] . $request['email']);
        $verification_link = "<a href='" . url('register/mode/' . $request['mode'] . '/verify/' . $token) . "'>" . "<button type=\"button\" class=\"btn btn-primary\">Click Here</button>" . "</a>";

        $user = User::where('username', $request['username']);
        if ($user->count() == 0) {
            $user = User::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'company_name' => $request['company_name'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'username' => $request['username'],
                'password' => bcrypt($password),
                'status' => $request["status"],
                'address' => $request["address"] ? $request["address"] : null,
                'city' => $request["city"] ? $request["city"] : null,
                'state' => $request["district"] ? $request["district"] : null,
                'country' => $request["country"] ? $request["country"] : null,
                'verification_link' => $token,
            ]);

            $this->tenant_lanuage_related_table_create($user);

            $mailer = new MegaMailer();
            $data = [
                'toMail' => $user->email,
                'toName' => $user->first_name,
                'customer_name' => $user->first_name,
                'verification_link' => $verification_link,
                'website_title' => $bs->website_title,
                'templateType' => 'email_verification',
                'type' => 'emailVerification'
            ];
            $mailer->mailFromAdmin($data);

            $package = Package::findOrFail($request['package_id']);
            Membership::create([
                'package_price' => $package->price,
                'discount' => session()->has('coupon_amount') ? session()->get('coupon_amount') : 0,
                'coupon_code' => session()->has('coupon') ? session()->get('coupon') : NULL,
                'price' => $amount,
                'currency' => $be->base_currency_text ?? "USD",
                'currency_symbol' => $be->base_currency_symbol ?? $be->base_currency_text,
                'payment_method' => $request["payment_method"],
                'transaction_id' => $transaction_id ?? 0,
                'status' => $request["status"] ? $request["status"] : 0,
                'is_trial' => $request["package_type"] == "regular" ? 0 : 1,
                'trial_days' => $request["package_type"] == "regular" ? 0 : $request["trial_days"],
                'receipt' => @$request["receipt_name"],
                'transaction_details' => $transaction_details ?? null,
                'settings' => json_encode($be),
                'package_id' => $request['package_id'],
                'user_id' => $user->id,
                'start_date' => Carbon::createFromFormat('d-m-Y', $request['start_date'], $timezone)->startOfDay(),
                'expire_date' => Carbon::createFromFormat('d-m-Y', $request['expire_date'], $timezone)->startOfDay(),
            ]);
            $features = json_decode($package->features, true);
            $features[] = "Contact";
            UserPermission::create([
                'package_id' => $request['package_id'],
                'user_id' => $user->id,
                'permissions' => json_encode($features)
            ]);

            User\BasicSetting::create([
                'email' => $user->email,
                'smtp_status' => 1,
                'email_address' => $user->email,
                'website_title' => $bs->website_title,
                'address' => $request["address"] ? $request["address"] : null,
                'base_currency_symbol_position' => $be->base_currency_symbol_position,
                'base_currency_symbol' => $be->base_currency_symbol ?? $be->base_currency_text,
                'base_currency_text' => $be->base_currency_text,
                'base_currency_text_position' => $be->base_currency_text_position,
                'base_currency_rate' => 1,
                'primary_color' => '0F172B',
                'secondary_color' => 'FEA116',
                'contact_number' => $request['phone'],
                'user_id' => $user->id,
                'reply_to' =>  $user->email,
                'from_name' => 'Hotelia',
                'user_id' => $user->id,
                'package_guest_checkout_status' => 1

            ]);

            AiUsageToken::create([
                'user_id' => $user->id,
                'total_tokens' => 0,
                'total_usable_tokens' => $package->total_ai_token,
                'extend_token' => 0
            ]);
        }

        // coupon update
        if (Session::has('coupon')) {
            $coupon = Coupon::where('code', Session::get('coupon'))->first();
            $coupon->total_uses = $coupon->total_uses + 1;
            $coupon->save();
        }
        return $user;
    }

    public function coupon(Request $request)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();

        if (session()->has('coupon')) {
            return __('Coupon already applied');
        }

        $coupon = Coupon::where('code', $request->coupon)->first();

        if (empty($coupon)) {
            return __('This coupon does not exist');
        }

        $coupon_count = $coupon->total_uses;

        if ($coupon->maximum_uses_limit != 999999) {
            if ($coupon_count >= $coupon->maximum_uses_limit) {
                return __('This coupon reached maximum limit');
            }
        }

        $start = Carbon::parse($coupon->start_date, $timezone)->startOfDay();
        $end = Carbon::parse($coupon->end_date, $timezone)->endOfDay();
        $today = Carbon::now($timezone)->startOfDay();

        $packages = $coupon->packages;
        $packages = json_decode($packages, true);
        $packages = !empty($packages) ? $packages : [];

        if (!in_array($request->package_id, $packages)) {
            return __('This coupon is not valid for this package');
        }

        if ($today->greaterThanOrEqualTo($start) && $today->lessThanOrEqualTo($end)) {
            $package = Package::find($request->package_id);
            $price = $package->price;

            if ($coupon->type == 'percentage') {
                $cAmount = ($price * $coupon->value) / 100;
            } else {
                $cAmount = $coupon->value;
            }

            Session::put('coupon', $request->coupon);
            Session::put('coupon_amount', round($cAmount, 2));

            return "success";
        } else {
            return __('This coupon does not exist');
        }
    }
    public function tenant_lanuage_related_table_create($user)
    {
        $adminLangs = AdminLaguage::get();
        $langCount = User\Language::where('user_id', $user->id)->where('is_default', 1)->count();
        $customerLangKeywords = file_get_contents(resource_path('lang/customer-lang.json'));
        if ($langCount == 0) {
            foreach ($adminLangs as $lang) {
                //======language create==========
                $language = User\Language::create([
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
}
