<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Services\Common;
use App\Constants\Constant;
use App\Models\User\Refund;
use Illuminate\Http\Request;
use App\Models\User\Language;
use App\Models\User\Whatsapp;
use App\Services\MailService;
use App\Models\User\RoomBooking;
use App\Models\User\BasicSetting;
use App\Models\User\BotShareInfo;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\DB;
use App\Models\User\OfflineGateway;
use App\Models\User\PaymentGateway;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\User\Coupon as RoomCoupon;
use App\Models\User\BookingAdjustment;
use App\Services\WpTemplateMessageSend;
use Illuminate\Support\Facades\Session;
use App\Services\Payment\PaymentHandler;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PaymentLinkController extends Controller
{
    /**
     * Send reply to user
     */
    public static function sendReply($wp_id, $to, $text)
    {
        $wp = Whatsapp::where('id', $wp_id)
            ->where('status', 1)
            ->first();

        $url = "https://graph.facebook.com/v22.0/{$wp->wp_phone_number}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $text]
        ];

        try {
            $response = Http::withToken($wp->wp_access_token)
                ->withOptions(['verify' => false, 'timeout' => 15])
                ->post($url, $payload);

            if (!$response->successful()) {
                Session::flash('error', $response->json('error.message'));
                return false;
            }

            Session::flash('success', "Payment link sent successfully");
            return true;
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            return false;
        }
    }

    /**
     * Payment Link Page
     * -payment_status(0) => not paid
     * -payment_status(1) => full paid
     * -payment_status(2) => advance paid
     * -payment_status(3) => cancelled
     */
    public function paymentRedirect($id)
    {
        $booking = RoomBooking::findOrFail($id);

        if ($booking->source == 'web') {
            return redirect()->route('frontend.booking_status.view', ['id' => $id]);
        }

        $data['bs'] = BasicSetting::select(
            'room_tax_status',
            'room_tax',
            'room_fee_status',
            'room_fee',
            'base_currency_symbol'
        )->where('user_id', $booking->user_id)->first();

        $booking = $this->syncBookingPricing($booking, $data['bs']);

        // check if there is any change in grand total or not
        $bookingAdjustment = BookingAdjustment::where('booking_id', $booking->id)->first();
        if ((int) $booking->payment_status === 1 && (!$bookingAdjustment || in_array($bookingAdjustment->type, ['refund', 'initial'], true))) {
            return redirect()->route('frontend.booking_status.view', ['id' => $id]);
        }

        $data['bookingAdjustment'] = $bookingAdjustment;

        $data['languages'] = Language::where('user_id', $booking->user_id)->get();
        $language = $this->defaultLang($booking->user_id);

        $date1 = new DateTime($booking->arrival_date);
        $date2 = new DateTime($booking->departure_date);
        $data['interval'] = $date1->diff($date2, true);

        $data['shareInfo'] = BotShareInfo::where('wp_id', $booking->wp_id)
            ->select('hotel_name', 'email_address', 'phone_numbers', 'locations')
            ->first();

        $data['booking'] = $booking;
        $data['currency_text'] = $booking->currency_text;
        $data['currency_text_position'] = $booking->currency_text_position;
        $data['payableAmount'] = $this->getPayableAmount($booking, $bookingAdjustment);
        $data['appliedCoupon'] = session()->get($this->couponSessionKey($booking->id), []);

        $roomInfo = $booking->hotelRoom()->first();
        $roomContentInfo = $roomInfo->roomContent()->where('language_id', $language->id)->first();
        $data['roomTitle'] = $roomContentInfo->title;

        $data['reserved_dates_info'] = $this->buildReservedDatesInfo($booking);


        //payment gateways
        $data['onlineGateways'] = PaymentGateway::where('status', 1)->get();
        $data['offlineGateways'] = OfflineGateway::where('status', 1)
            ->orderBy('serial_number')
            ->get();

        $stripeInfo = PaymentGateway::where('keyword', 'stripe')->value('information');
        $stripeInfo = $stripeInfo ? json_decode($stripeInfo, true) : null;
        $data['stripe_key'] = $stripeInfo['key'] ?? null;

        $authorizeInfo = PaymentGateway::where('keyword', 'authorize.net')->value('information');
        $authorizeInfo = $authorizeInfo ? json_decode($authorizeInfo, true) : null;

        if ($authorizeInfo) {
            $data['anetSrc'] = $authorizeInfo['sandbox_check'] == 1
                ? 'https://jstest.authorize.net/v1/Accept.js'
                : 'https://js.authorize.net/v1/Accept.js';

            $data['authorizeClientKey'] = $authorizeInfo['public_key'] ?? null;
            $data['authorizeLoginId']   = $authorizeInfo['login_id'] ?? null;
        }

        $data['userBs'] = DB::table('user_basic_settings')
            ->where('user_id', $booking->user_id)
            ->select('favicon', 'primary_color', 'secondary_color', 'website_title', 'logo')
            ->first();

        $data['defaultLang'] = $language;
        return view('user-front.payment-link', $data);
    }

    /**
     * Apply coupon for a payment-link booking.
     */
    public function applyCoupon($id, Request $request)
    {
        $booking = RoomBooking::findOrFail($id);
        $booking = $this->syncBookingPricing($booking);
        $defaultLang = $this->defaultLang($booking->user_id);
        $keywords = json_decode($defaultLang->keywords, true);

        $validator = Validator::make(
            $request->all(),
            ['coupon' => 'required|string|max:255'],
            ['coupon.required' => ($keywords['The coupon field is required'] ?? 'The coupon field is required') . '.']
        );

        if ($validator->fails()) {
            return Response::json(['errors' => $validator->getMessageBag()->toArray()], 422);
        }

        if ((int) $booking->payment_status === 1 || (int) $booking->payment_status === 3) {
            return Response::json([
                'errors' => [
                    'coupon' => [($keywords['This booking is not eligible for coupon'] ?? 'This booking is not eligible for coupon') . '.']
                ]
            ], 422);
        }

        $couponCode = trim((string) $request->coupon);
        $coupon = RoomCoupon::where('user_id', $booking->user_id)
            ->where('code', $couponCode)
            ->first();

        if (!$coupon) {
            return Response::json([
                'errors' => [
                    'coupon' => [($keywords['This coupon does not exist'] ?? 'This coupon does not exist') . '.']
                ]
            ], 422);
        }

        $timezone = TimzeZoneService::getUserTimeZone($booking->user_id);
        $today = Carbon::now($timezone)->startOfDay();
        $startDate = Carbon::parse($coupon->start_date, $timezone)->startOfDay();
        $endDate = Carbon::parse($coupon->end_date, $timezone)->endOfDay();

        if ($today->lt($startDate) || $today->gt($endDate)) {
            return Response::json([
                'errors' => [
                    'coupon' => [($keywords['This coupon is not valid right now'] ?? 'This coupon is not valid right now') . '.']
                ]
            ], 422);
        }

        $roomIds = json_decode((string) $coupon->rooms, true);
        if (is_array($roomIds) && count($roomIds) > 0) {
            $roomIds = array_map('intval', $roomIds);
            if (!in_array((int) $booking->room_category_id, $roomIds, true)) {
                return Response::json([
                    'errors' => [
                        'coupon' => [($keywords['This coupon is not valid for this room'] ?? 'This coupon is not valid for this room') . '.']
                    ]
                ], 422);
            }
        }

        $amountToPay = $this->getPayableAmount($booking);
        if ($amountToPay <= 0) {
            return Response::json([
                'errors' => [
                    'coupon' => [($keywords['No payable amount found for this booking'] ?? 'No payable amount found for this booking') . '.']
                ]
            ], 422);
        }

        $discount = strtolower((string) $coupon->type) === 'percentage'
            ? ($amountToPay * (float) $coupon->value) / 100
            : (float) $coupon->value;

        $discount = round(min($discount, $amountToPay), 2);
        $netAmount = round($amountToPay - $discount, 2);

        if ($netAmount <= 0) {
            return Response::json([
                'errors' => [
                    'coupon' => [($keywords['Coupon discount is too high for this payable amount'] ?? 'Coupon discount is too high for this payable amount') . '.']
                ]
            ], 422);
        }

        session()->put($this->couponSessionKey($booking->id), [
            'code' => $coupon->code,
            'discount' => $discount,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
        ]);

        return Response::json([
            'status' => 'success',
            'message' => ($keywords['Coupon applied successfully'] ?? 'Coupon applied successfully') . '.',
            'discount' => $discount,
            'amount_to_pay' => $netAmount,
            'coupon_code' => $coupon->code,
        ]);
    }

    /**
     * Submit payment form and process payment
     */
    public function paymentProcess($id, PaymentService $paymentService, Request $request)
    {
        $booking = RoomBooking::findOrFail($id);
        $booking = $this->syncBookingPricing($booking);
        $defaultLang = $this->defaultLang($booking->user_id);
        $keywords = json_decode($defaultLang->keywords, true);

        $rules = [
            'payment_method' => 'required',
        ];

        if ($request->payment_method === 'stripe') {
            $rules['stripeToken'] = 'required';
        }

        if ($request->payment_method === 'iyzico') {
            $rules['identity_number'] = 'required';
            $rules['address'] = 'required';
            $rules['zip_code'] = 'required';
            $rules['country'] = 'required';
            $rules['city'] = 'required';
        }

        function t($key, $keywords)
        {
            return ($keywords[$key] ?? $key) . '.';
        }

        $messages = [
            'payment_method.required' => t('The payment method field is required', $keywords),
            'stripeToken.required'    => t('Stripe token is required', $keywords),
            'identity_number.required' => t('Identity number is required', $keywords),
            'address.required'        => t('Address is required', $keywords),
            'zip_code.required'       => t('Zip code is required', $keywords),
            'country.required'        => t('Country is required', $keywords),
            'city.required'           => t('City is required', $keywords),
        ];



        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return Response::json(
                [
                    'errors' => $validator->getMessageBag()->toArray()
                ],
                400
            );
        }

        $amount = $this->getPayableAmount($booking);

        $appliedCoupon = session()->get($this->couponSessionKey($booking->id), []);
        if (is_array($appliedCoupon) && !empty($appliedCoupon['discount'])) {
            $amount = max(0, round((float) $amount - (float) $appliedCoupon['discount'], 2));
        }

        if ($amount <= 0) {
            return response()->json([
                'status' => 'currency-error',
                'message' => t('No payable amount found for this booking', $keywords),
            ]);
        }

        $bs = BasicSetting::where('user_id', $booking->user_id)
            ->select('base_currency_text', 'base_currency_rate', 'user_id')
            ->first();

        $gateway = strtolower(trim((string) $request->payment_method));
        $response = PaymentHandler::checkGateway($gateway, $amount, $bs, $keywords);

        //currency check
        if ($response['status'] === 'error') {
            return response()->json([
                'status' => 'currency-error',
                'message' => t($response['message'], $keywords),
            ]);
        }

        $successUrl = match ($gateway) {
            'razorpay'  => route('frontend.razorpay.success'),
            'paytm'     => route('frontend.paytm.success'),
            'paytabs'   => route('frontend.paytabs.success'),
            'iyzico'    => route('frontend.iyzico.success'),
            default     => route('room_booking.success') . '?gateway=' .  $response['gateway'] . '&booking_id=' . $booking->id,
        };

        $data = [
            'payment_for' => 'Room Booking',
            'amount' => $amount,
            'formatted_amount' => $response['amount'],
            'booking_id' => $id,
            'user_id' => $booking->user_id,
            'customer_name' => $booking->customer_name,
            'customer_phone' => $booking->customer_phone,
            'customer_email' => $booking->customer_email,
            'gateway' => $gateway,
            'is_admin' => 0, // 0 = user, 1 = admin (use for get paymentgateway info)
            //success and cancel url
            'success_url' => $successUrl,
            'cancel_url' => route('room_booking.cancel') . '?gateway=' . $gateway . '&booking_id=' . $booking->id,
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
        ];

        $link = $paymentService->pay($data);

        return normalizePaymentResponse($request, $link);
    }

    /**
     * payment success and cancel logic route
     */
    public function paymentSuccess(Request $request, PaymentService $paymentService)
    {
        $gateway = $request->query('gateway');
        $metadata = $paymentService->handleSuccess($request, $gateway);

        //For Iyzico, we are sending payment complete message from cron job after checking payment status
        if ($metadata != 'Iyzico') {
            WpTemplateMessageSend::paymentCompleteMessage($metadata['booking_id']);
            $this->clearCouponSession($metadata['booking_id']);
        }
        return redirect()->route('frontend.payment_success.view', ['id' => $metadata['booking_id']]);
    }

    public function paymentCancel(Request $request, PaymentService $paymentService)
    {
        $gateway = $request->query('gateway');
        $metadata = $paymentService->handleCancel($request, $gateway);

        return redirect()->route('frontend.payment_cancel.view', ['id' => $request->booking_id]);
    }

    /**
     * razorpay notify function
     */
    public function razorpayBookingSuccess(Request $request, PaymentService $paymentService)
    {
        $bookingId = Session::get('bookingId');
        $metadata = $paymentService->handleSuccess($request, 'razorpay');

        if (!$metadata || empty($metadata['booking_id'])) {
            if ($bookingId) {
                return redirect()->route('frontend.payment_cancel.view', ['id' => $bookingId]);
            }
            return redirect()->route('room_booking.cancel', ['gateway' => 'razorpay']);
        }

        return redirect()->route('frontend.payment_success.view', ['id' => $metadata['booking_id']]);
    }
    /**
     * paytm notify function
     */
    public function paytmBookingSuccess(Request $request, PaymentService $paymentService)
    {
        $bookingId = Session::get('bookingId');
        $metadata = [];
        if ($request->STATUS == 'TXN_SUCCESS') {
            $metadata = $paymentService->handleSuccess($request, 'paytm');
        }
        if (!empty($metadata['booking_id']) && $bookingId) {
            return redirect()->route('frontend.payment_success.view', ['id' => $bookingId]);
        }

        $paymentService->handleCancel($request, 'paytm');
        return redirect()->route('frontend.payment_cancel.view', ['id' => $bookingId]);
    }
    /**
     * paytabs notify function
     */
    public function paytabsBookingSuccess(Request $request, PaymentService $paymentService)
    {
        $metadata = Session::get('metadata', []);
        if ($request['respStatus'] == "A" && $request['respMessage'] == 'Authorised') {
            $metadata = $paymentService->handleSuccess($request, 'paytabs');
            return redirect()->route('frontend.payment_success.view', ['id' => $metadata['booking_id']]);
        }
        return redirect()->route('frontend.payment_cancel.view', ['id' => $metadata['booking_id']]);
    }
    /**
     * iyzico notify function
     */
    public function iyzicoBookingSuccess(Request $request, PaymentService $paymentService)
    {
        $metadata = Session::get('metadata', []);
        if (!empty($metadata)) {
            $metadata = $paymentService->handleSuccess($request, 'iyzico');
            return redirect()->route('frontend.payment_success.view', ['id' => $metadata['booking_id']]);
        }
        return redirect()->route('frontend.payment_cancel.view', ['id' => $metadata['booking_id']]);
    }
    /**
     * payment success and cancel view page display
     */
    public function viewsuccess($id)
    {
        $data['booking'] = RoomBooking::findOrFail($id);
        $whatsapp = Whatsapp::query()
            ->where('id', $data['booking']->wp_id)
            ->select('wp_from_number')
            ->first();
        $whatsappNumber = preg_replace('/\D+/', '', (string) optional($whatsapp)->wp_from_number);
        $data['whatsappOpenUrl'] = !empty($whatsappNumber) ? 'https://wa.me/' . $whatsappNumber : null;

        $data['userBs'] = DB::table('user_basic_settings')
            ->where('user_id', $data['booking']->user_id)
            ->select('favicon', 'primary_color', 'secondary_color', 'website_title', 'logo')
            ->first();
        $data['languages'] = Language::where('user_id', $data['booking']->user_id)->get();

        $data['defaultLang'] = $this->defaultLang($data['booking']->user_id);
        return view('user-front.success', $data);
    }
    public function viewcancel($id)
    {
        $data['booking'] = RoomBooking::findOrFail($id);
        $data['userBs'] = DB::table('user_basic_settings')
            ->where('user_id', $data['booking']->user_id)
            ->select('favicon', 'primary_color', 'secondary_color', 'website_title', 'logo')
            ->first();
        $data['languages'] = Language::where('user_id', $data['booking']->user_id)->get();

        $data['defaultLang'] = $this->defaultLang($data['booking']->user_id);
        return view('user-front.cancel', $data);
    }

    /**
     * If alrady paid or cancelled
     */
    public function bookingStatus($id)
    {
        $booking = RoomBooking::findOrFail($id);
        $booking = $this->syncBookingPricing($booking);

        // check if there is any change in grand total or not
        $bookingAdjustment = BookingAdjustment::where('booking_id', $booking->id)->first();
        if ($booking->source == 'whatsapp_bot') {
            if ($booking->payment_status != 1 || optional($bookingAdjustment)->type == 'extra_payment') {
                return redirect()->route('payment.redirect', ['id' => $id]);
            }
        }


        $language = $this->defaultLang($booking->user_id);
        $date1 = new DateTime($booking->arrival_date);
        $date2 = new DateTime($booking->departure_date);
        $data['interval'] = $date1->diff($date2, true);
        $data['success'] = $booking->payment_status = 1 ? true : false;
        $data['pageHeading'] = __('Booking Status');
        $data['shareInfo'] = BotShareInfo::where('wp_id', $booking->wp_id)
            ->select('hotel_name', 'email_address', 'phone_numbers', 'locations')
            ->first();

        $data['currency_text'] = $booking->currency_text;
        $data['currency_text_position'] = $booking->currency_text_position;

        $roomInfo = $booking->hotelRoom()->first();
        $roomContentInfo = $roomInfo->roomContent()->where('language_id', $language->id)->first();
        $data['roomTitle'] = $roomContentInfo->title;

        $data['reserved_dates_info'] = $this->buildReservedDatesInfo($booking);
        $data['languages'] = Language::where('user_id', $booking->user_id)->get();
        $data['booking'] = $booking;
        $data['invoiceDownloadUrl'] = $this->getBookingInvoiceUrl($booking);
        $data['userBs'] = DB::table('user_basic_settings')
            ->where('user_id', $booking->user_id)
            ->select('favicon', 'primary_color', 'secondary_color', 'website_title', 'logo')
            ->first();

        $data['defaultLang'] = $this->defaultLang($data['booking']->user_id);

        $data['bookingAdjustment'] = BookingAdjustment::where('booking_id', $booking->id)->first();

        $data['refunds'] = Refund::Where('booking_id', $booking->id)->orderBy('id', 'desc')
            ->get();

        $data['userBs'] = BasicSetting::select(
            'room_tax_status',
            'room_tax',
            'room_fee_status',
            'room_fee',
            'base_currency_symbol'
        )->where('user_id', $booking->user_id)->first();

        return view('user-front.already-paid', $data);
    }

    /**
     * Ensure a paid booking has an invoice and return its public URL.
     */
    private function getBookingInvoiceUrl(RoomBooking $booking): ?string
    {
        if ((int) $booking->payment_status !== 1) {
            return null;
        }

        $invoiceName = $booking->invoice;
        $invoicePath = !empty($invoiceName)
            ? public_path(Constant::WEBSITE_ROOM_BOOKING_INVOICE . '/' . $invoiceName)
            : null;

        if (empty($invoiceName) || !file_exists($invoicePath)) {
            $invoiceName = MailService::generateBookingInvocie($booking);

            if (empty($invoiceName)) {
                return null;
            }

            $booking->update(['invoice' => $invoiceName]);
        }

        return asset(Constant::WEBSITE_ROOM_BOOKING_INVOICE . '/' . $invoiceName);
    }

    /**
     * Get payable amount for payment link according to booking state.
     */
    private function getPayableAmount(RoomBooking $booking, ?BookingAdjustment $bookingAdjustment = null): float
    {
        $booking = $this->syncBookingPricing($booking);

        $amount = ((int) $booking->advance_payment_status === 1)
            ? (float) $booking->advance_amount
            : (float) $booking->due;

        $bookingAdjustment = $bookingAdjustment ?: BookingAdjustment::where('booking_id', $booking->id)->first();
        if ($bookingAdjustment && $bookingAdjustment->type === 'extra_payment') {
            $amount = (float) $bookingAdjustment->amount;
        }

        return round(max($amount, 0), 2);
    }

    /**
     * Recalculate booking totals for unassigned unpaid bookings so payment links
     * can work before room numbers are assigned.
     */
    private function syncBookingPricing(RoomBooking $booking, ?BasicSetting $bs = null): RoomBooking
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

        $bs = $bs ?: BasicSetting::select(
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

        $room = $booking->hotelRoom()->first(['id', 'payment_system', 'advance_amount']);
        if (!$room) {
            return false;
        }

        return true;
    }

    private function hasAssignedRooms(RoomBooking $booking): bool
    {
        return !empty($this->decodeReservedDatesInfo($booking->reserved_dates_info));
    }

    private function buildReservedDatesInfo(RoomBooking $booking)
    {
        $reservedDatesInfo = collect($this->decodeReservedDatesInfo($booking->reserved_dates_info))
            ->sortBy('date')
            ->map(function ($item) {
                $date = is_array($item) ? ($item['date'] ?? null) : ($item->date ?? null);
                $roomNumber = is_array($item)
                    ? ($item['room_number'] ?? $item['room_no'] ?? $item['room_numberno'] ?? 'N/A')
                    : ($item->room_number ?? $item->room_no ?? $item->room_numberno ?? 'N/A');

                return (object) [
                    'date' => $date,
                    'room_number' => $roomNumber,
                    'formatted_date' => $date ? Carbon::parse($date)->format('d M, Y') : null,
                ];
            })
            ->values();

        if ($reservedDatesInfo->isNotEmpty()) {
            return $reservedDatesInfo;
        }

        $dates = collect();
        $currentDate = Carbon::parse($booking->arrival_date);
        $lastDate = Carbon::parse($booking->departure_date)->subDay();

        while ($currentDate->lte($lastDate)) {
            $dates->push((object) [
                'date' => $currentDate->format('Y-m-d'),
                'room_number' => 'Not assigned yet',
                'formatted_date' => $currentDate->format('d M, Y'),
            ]);

            $currentDate->addDay();
        }

        return $dates;
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

    private function couponSessionKey(int $bookingId): string
    {
        return 'payment_link_coupon_' . $bookingId;
    }

    private function clearCouponSession(int $bookingId): void
    {
        session()->forget($this->couponSessionKey($bookingId));
    }

    /**
     * change language
     */
    public function langaugeChange($code, $user_id)
    {
        session()->put('user_lang_' . $user_id, $code);
        return redirect()->back();
    }


    /**
     * get language
     */
    private function defaultLang($user_id)
    {
        $code = session()->get('user_lang_' . $user_id);

        if (!is_null($code)) {
            $defaultLang = Language::where('user_id', $user_id)->where('code', $code)->first();
        } else {
            $defaultLang = Language::where('user_id', $user_id)->where('is_default', 1)->first();
        }

        return $defaultLang;
    }
}
