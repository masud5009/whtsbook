<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class YocoGateway implements PaymentGatewayInterface
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

        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'yoco');

        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $paydata['secret_key'],
        ])->post('https://payments.yoco.com/api/checkouts', [
            'amount' => $data['formatted_amount'],
            'currency' => 'ZAR',
            'successUrl' => $success_url,
            'cancelUrl' => $cancel_url
        ]);
        $responseData = $response->json();

        if (array_key_exists('redirectUrl', $responseData)) {

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
                'yoco_id' => $responseData['id'],
                's_key' => $paydata['secret_key'],
                'is_admin' => $data['is_admin'],
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
            //redirect for received payment from user
            return $responseData["redirectUrl"];
        } else {
            return redirect($cancel_url);
        }
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');

        $paydata =  PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'yoco');
        if ($paymentPayload['yoco_id'] && $paydata['secret_key'] == $paymentPayload['s_key']) {

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details =  null;

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;

            return $paymentPayload;
        } else {
            return $paymentPayload['cancel_url'];
        }
    }

    public function cancel(Request $request) {}
}
