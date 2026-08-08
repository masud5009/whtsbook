<?php

namespace App\Services\Payment\Gateways;

use Exception;
use Illuminate\Http\Request;
use App\Http\Helpers\Instamojo;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class InstamojoGateway implements PaymentGatewayInterface
{
    public function initialize(array $data)
    {
        $amount = $data['formatted_amount'];
        $user_id = @$data['user_id'];

        if ($data['payment_for'] == 'Room Booking') {
            $countRoomBooking = LimitCheckerHelper::roomBookingCountUser($user_id);
            $roomBookingLimit = LimitCheckerHelper::roomBookingsLimit($user_id);
            if (!($roomBookingLimit > $countRoomBooking)) {
                return redirect()->back()->with('error', __('Please Contact Support'));
            }
        }

        $instamojoData = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'instamojo');

        $endpoint = ($instamojoData['sandbox_check'] == 1)
            ? 'https://test.instamojo.com/api/1.1/'
            : 'https://www.instamojo.com/api/1.1/';

        $api = new Instamojo($instamojoData['key'], $instamojoData['token'], $endpoint);

        $notify_url = $data['success_url'];

        try {
            $response = $api->paymentRequestCreate([
                'purpose'      => $data['payment_for'],
                'amount'       => number_format($amount, 2, '.', ''),
                'buyer_name'   => $data['customer_name'],
                'email'        => $data['customer_email'],
                'send_email'   => false,
                'phone'        => $data['customer_phone'],
                'send_sms'     => false,
                'redirect_url' => $notify_url,
            ]);

            $paymentPayload = [
                'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
                'amount' => $data['amount'],
                'user_id' => @$data['user_id'],
                'booking_id' => @$data['booking_id'],
                'tokens' => @$data['tokens'],
                'payment_method' => 'Instamojo',
                'cancel_url' => $data['cancel_url'],
'gateway_type' => $data['gateway_type'],
                'paymentId' => $response['id'],
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

            return $response['longurl'];
        } catch (Exception $e) {
            return $data['cancel_url'];
        }
    }


    public function success(Request $request)
    {
        // get the information from session
        $paymentPayload = session()->get('paymentPayload');

        $urlInfo = $request->all();

        if ($urlInfo['payment_request_id'] == $paymentPayload['paymentId']) {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($urlInfo['payment_request_id']);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;
            return $paymentPayload;
        } else {
            return $paymentPayload['cancel_url'];
        }
    }

    public function cancel(Request $request) {}
}
