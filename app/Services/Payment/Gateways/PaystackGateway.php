<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class PaystackGateway implements PaymentGatewayInterface
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

        $paystackData = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'paystack');
        $api_key = $paystackData['key'];

        $notify_url = $data['success_url'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => 'https://api.paystack.co/transaction/initialize',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode([
                'amount'       => $amount,
                'email'        => $data['customer_email'],
                'callback_url' => $notify_url
            ]),
            CURLOPT_HTTPHEADER     => [
                'authorization: Bearer ' . $api_key,
                'content-type: application/json',
                'cache-control: no-cache'
            ]
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $transaction = json_decode($response, true);

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

        if ($transaction['status'] == true) {
            return $transaction['data']['authorization_url'];
        } else {
            return $data['cancel_url'];
        }
    }

    public function success(Request $request)
    {
        // get the information from session
        $paymentPayload = Session::get('paymentPayload');
        $urlInfo = $request->all();

        if ($urlInfo['trxref'] === $urlInfo['reference']) {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($urlInfo['trxref']);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;
            return $paymentPayload;
        } else {
            return  $paymentPayload['cancel_url'];
        }
    }

    public function cancel(Request $request) {}
}
