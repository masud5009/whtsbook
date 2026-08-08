<?php

namespace App\Models\User;

use Carbon\Carbon;
use App\Services\MailService;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Services\BookingAdjustmentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomBooking extends Model
{
    use HasFactory;
    protected $table = 'user_room_bookings';
    protected $guarded = [];

    protected $casts = [
        'reserved_dates_info' => 'array',
    ];

    public function hotelRoom()
    {
        return $this->belongsTo('App\Models\User\Room', 'room_category_id', 'id');
    }

    public function roomBookedByUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    /**
     * Make booking
     */
    public static function storeBooking($request)
    {
        // store the room booking information in database
        $dateArray = explode(' ', $request->dates);

        $timezone = TimzeZoneService::getUserTimeZone(Auth::guard('web')->user()->id);
        $arrivalDate = Carbon::parse($dateArray[0], $timezone)->format('Y-m-d');
        $departureDate = Carbon::parse($dateArray[2], $timezone)->format('Y-m-d');

        $onlinePaymentGateway = ['PayPal', 'Stripe', 'Instamojo', 'Paystack', 'Flutterwave', 'Razorpay', 'MercadoPago', 'Mollie', 'Paytm'];
        $gatewayType = in_array($request->payment_method, $onlinePaymentGateway) ? 'online' : 'Offline';

        $userBs = DB::table('user_basic_settings')
            ->where('user_id', Auth::guard('web')->user()->id)
            ->select(
                'room_tax_status',
                'room_tax',
                'room_fee_status',
                'room_fee',
                'base_currency_symbol',
                'base_currency_symbol_position',
                'base_currency_text',
                'base_currency_text_position',
            )
            ->first();

        $tax_percentage = $userBs->room_tax_status == 1 ? $userBs->room_tax : 0;
        $room_fee = $userBs->room_fee_status == 1 ? $userBs->room_fee : 0;
        $tax_amount = ($request->total * $tax_percentage) / 100;
        $grand_total = $request->total + $tax_amount + $room_fee;

        //0=>unpaid,1=>full-paid,2=>partial-paid,3=>cancelled
        $payment_status = (int) $request->payment_status;
        $paid_amount = 0.00;
        $due = $grand_total;

        if ($payment_status === 1) {
            $paid_amount = $grand_total;
            $due = 0.00;
        } elseif ($payment_status === 2) {
            $partial = (float) ($request->paying_amount ?? 0);
            $partial = max(0, min($partial, $grand_total));

            $paid_amount = $partial;
            $due = $grand_total - $partial;
        } elseif ($payment_status === 3) {
            $paid_amount = 0.00;
            $due = 0.00;
        }

        $roomCategory = DB::table('user_room_categories')
            ->where('id', $request->room_category_id)
            ->select('payment_system', 'advance_amount')
            ->first();
        $advance_amount = 0;
        if ($roomCategory->payment_system == 'advance') {
            $advance_amount = $roomCategory->advance_amount;
        }

        $bookingInfo = self::create([
            'booking_number' => 'WB' . time() . rand(100, 999),
            'user_id' => Auth::guard('web')->user()->id,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'room_category_id' => $request->room_category_id,
            'arrival_date' => $arrivalDate,
            'departure_date' => $departureDate,
            'adult' => $request->adult,
            'child' => $request->child,

            'total_rent' => $request->total,
            'discount' => $request->discount ?? 0.00,
            'tax_amount' => $tax_amount,
            'tax_percentage' => $tax_percentage,
            'fee' => $room_fee,
            'grand_total' => $grand_total,
            'paid_amount' => $paid_amount,
            'due' => $due,
            'partial_amount' => $payment_status === 2 ? $paid_amount : 0.00,
            'advance_amount' => $advance_amount,

            'currency_symbol' => $userBs->base_currency_symbol,
            'currency_symbol_position' => $userBs->base_currency_symbol_position,
            'currency_text' => $userBs->base_currency_text,
            'currency_text_position' => $userBs->base_currency_text_position,
            'payment_method' => $request->payment_method,
            'gateway_type' => $gatewayType,
            'reserved_dates_info' => $request->rooms_json,
            'total_rooms' => $request->total_rooms ?? 1,
            'source' => 'web',
            'payment_status' => $request->payment_status,
            'booking_status' => $request->booking_status,
            'advance_payment_status' => $payment_status == 2 ? 1 : 0,
            'created_at' => Carbon::now($timezone),
            'updated_at' => Carbon::now($timezone),
        ]);

        // send mail to customer if payment status is full paid
        if ($request->payment_status == 1) {
            $invoice = MailService::generateBookingInvocie($bookingInfo);
            $bookingInfo->update(['invoice' => $invoice]);
            $templateType = $bookingInfo->gateway_type == 'Offline' ? 'room_booking_for_offline_gateway' : 'room_booking_for_online_gateway';
            MailService::sendBookingMail($bookingInfo, $templateType);
        }

        //Update booking adjustment
        BookingAdjustmentService::store($bookingInfo);

        return $bookingInfo;
    }

    /**
     * Update booking
     */
    public static function updateBooking($request)
    {
        $booking = self::find($request->booking_id);

        [$startDate, $endDate] = explode(' - ', $request->dates);

        $timezone = TimzeZoneService::getUserTimeZone(Auth::guard('web')->user()->id);
        $startDate = Carbon::parse($startDate, $timezone)->format('Y-m-d');
        $endDate = Carbon::parse($endDate, $timezone)->format('Y-m-d');

        $onlinePaymentGateway = ['PayPal', 'Stripe', 'Instamojo', 'Paystack', 'Flutterwave', 'Razorpay', 'MercadoPago', 'Mollie', 'Paytm'];
        $gatewayType = in_array($request->payment_method, $onlinePaymentGateway) ? 'online' : 'Offline';

        $userBs = DB::table('user_basic_settings')
            ->where('user_id', Auth::guard('web')->user()->id)
            ->select(
                'room_tax_status',
                'room_tax',
                'room_fee_status',
                'room_fee',
                'base_currency_symbol',
                'base_currency_symbol_position',
                'base_currency_text',
                'base_currency_text_position',
            )
            ->first();

        $tax_percentage = $userBs->room_tax_status == 1 ? $userBs->room_tax : 0;
        $room_fee = $userBs->room_fee_status == 1 ? $userBs->room_fee : 0;
        $tax_amount = ($request->total * $tax_percentage) / 100;
        $grand_total = $request->total + $tax_amount + $room_fee;

        //0=>unpaid,1=>full-paid,2=>partial-paid,3=>cancelled
        $payment_status = (int) $request->payment_status;
        $paid_amount = 0.00;
        $due = $grand_total;

        if ($payment_status == 1) {
            $paid_amount = $grand_total;
            $due = 0.00;
        } elseif ($payment_status == 2 && $request->paying_amount > 0) {
            $partial = (float) ($request->paying_amount ?? 0);
            $partial = max(0, min($partial, $grand_total));

            $paid_amount = $partial;
            if ($booking->due > 0) {
                $due = $grand_total - $partial;
            } else {
                $due = 0.00;
            }
        } elseif ($payment_status == 3) {
            $paid_amount = 0.00;
            $due = 0.00;
        }

        // get the payment system and advance amount for the selected room category
        $roomCategory = DB::table('user_room_categories')
            ->where('id', $request->room_category_id)
            ->select('payment_system', 'advance_amount')
            ->first();
        $advance_amount = 0;
        if ($roomCategory->payment_system == 'advance' && $booking->partial_amount < 0) {
            $advance_amount = $roomCategory->advance_amount;
        } else {
            $advance_amount = 0;
        }

        if ($payment_status == 2 && $request->paying_amount > 0) {
            $advance_payment_status = 2;
        } else {
            $advance_payment_status = 0;
        }
        // dd($booking->paid_amount, $paid_amount);

        session()->put('previous_paid_amount', $booking->paid_amount);

        // update the room booking information in database
        $booking->update([
            'booking_number' => $booking->booking_number,
            'user_id' => Auth::guard('web')->user()->id,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'room_category_id' => $request->room_category_id,
            'arrival_date' => $startDate,
            'departure_date' => $endDate,
            'adult' => $request->adult,
            'child' => $request->child,

            'total_rent' => $request->total,
            'discount' => $request->discount ?? 0.00,
            'tax_amount' => $tax_amount,
            'tax_percentage' => $tax_percentage,
            'fee' => $room_fee,
            'grand_total' => $grand_total,
            'paid_amount' => $paid_amount,
            'due' => $due,
            'partial_amount' => $payment_status === 2 ? $paid_amount : 0.00,
            'advance_amount' => $advance_amount,

            'currency_symbol' => $userBs->base_currency_symbol,
            'currency_symbol_position' => $userBs->base_currency_symbol_position,
            'currency_text' => $userBs->base_currency_text,
            'currency_text_position' => $userBs->base_currency_text_position,
            'payment_method' => $request->payment_method,
            'gateway_type' => $gatewayType,
            'reserved_dates_info' => $request->rooms_json,
            'total_rooms' => $request->total_rooms ?? 1,
            'source' => $booking->source ?: 'web',
            'payment_status' => $request->payment_status,
            'booking_status' => $request->booking_status,
            'advance_payment_status' => $advance_payment_status,
            'updated_at' => Carbon::now($timezone),
        ]);
        // send mail to customer if payment status is full paid
        if ($request->payment_status == 1) {
            $invoice = MailService::generateBookingInvocie($booking);
            $booking->update(['invoice' => $invoice]);
            $templateType = $booking->gateway_type == 'Offline' ? 'room_booking_for_offline_gateway' : 'room_booking_for_online_gateway';
            MailService::sendBookingMail($booking, $templateType);
        }

        //Update booking adjustment and send whatsapp message if there is a change in total rent
        if (!is_null($booking->reserved_dates_info) && $booking->reserved_dates_info != '[]') {
            BookingAdjustmentService::update($request->booking_id);
        }
        return [
            'status' => true,
            'booking_id' => $booking->id
        ];
    }

    public function getFormattedBookingStatusAttribute()
    {
        if ($this->booking_status == 0) {
            return __('Pending');
        } elseif ($this->booking_status == 1) {
            return __('Confirmed');
        } else {
            return __('Rejected');
        }
    }

    public function getFormattedPaymentStatusAttribute()
    {
        if ($this->payment_status == 0) {
            return __('Pending');
        } elseif ($this->payment_status == 1) {
            return __('Success');
        } elseif ($this->payment_status == 2) {
            return __('Partial');
        } elseif ($this->payment_status == 3) {
            return __('Rejected');
        } else {
            return '';
        }
    }
}
