<?php

namespace App\Services\Payment\Gateways;

use Midtrans\Snap;
use Illuminate\Http\Request;
use Midtrans\Config as MidtransConfig;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class MidtransGateway implements PaymentGatewayInterface
{
    public function initialize(array $data)
    {
        $user_id = @$data['user_id'];

        if ($data['payment_for'] == 'Room Booking') {
            $countRoomBooking = LimitCheckerHelper::roomBookingCountUser($user_id);
            $roomBookingLimit = LimitCheckerHelper::roomBookingsLimit($user_id);
            if (!($roomBookingLimit > $countRoomBooking)) {
                return redirect()->back()->with('error', __('Please Contact Support'));
            }
        }

        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'midtrans');

        $name = $data['customer_name'];
        $email = $data['customer_email'];
        $phone = $data['customer_phone'];

        // will come from database
        MidtransConfig::$serverKey = $paydata['server_key'];
        MidtransConfig::$isProduction = $paydata['is_production'] == 0 ? true : false;
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;
        $token = uniqid();
        Session::put('token', $token);
        $params = [
            'transaction_details' => [
                'order_id' => $token,
                'gross_amount' => $data['formatted_amount'], // will be multiplied by 1000
            ],
            'customer_details' => [
                'first_name' => $name,
                'email' => $email,
                'phone' => $phone,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        // put some data in session before redirect to midtrans url
        if (
            $paydata['is_production'] == 1
        ) {
            $is_production = $paydata['is_production'];
        }
        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];

        $midtrans_data['snapToken'] = $snapToken;
        $midtrans_data['is_production'] = $is_production;
        $midtrans_data['success_url'] = $success_url;
        $midtrans_data['cancel_url'] = $cancel_url;
        $midtrans_data['client_key'] = $paydata['server_key'];
        $midtrans_data['title'] = 'Midtrans Payment';

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'Paystack',
            'cancel_url' => $data['cancel_url'],
            'gateway_type' => $data['gateway_type'],
            //membership data
            'package_id' => @$data['package_id'],
            'start_date' => @$data['start_date'],
            'expire_date' => @$data['expire_date'],
            'status' => @$data['status'],
            'password' => @$data['password'],
            'mode' => @$data['mode'],
            'district' => @$data['district'],
            'first_name' => @$data['first_name'],
            'last_name' => @$data['last_name'],
            'package_type' => @$data['package_type'],
            'username' => @$data['username'],
            'email' => @$data['customer_email'],
'is_admin' => @$data['is_admin'],
            'company_name' => @$data['company_name'],
            'phone' => @$data['customer_phone'],
            'address' => @$data['address'],
            'city' => @$data['city'],
            'district' => @$data['district'],
            'country' => @$data['country'],
        ];
        Session::put('paymentPayload', $paymentPayload);


        return view('front.booking-payment.midtrans', $midtrans_data);
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');
        $token = Session::get('token');
        if ($request->status_code == 200 && $token == $request->order_id) {

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($request->all());

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;


            return $paymentPayload;
        } else {
            return false;
        }
    }

    public function cancel(Request $request) {}
}
