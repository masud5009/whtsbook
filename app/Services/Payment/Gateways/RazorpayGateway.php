<?php

namespace App\Services\Payment\Gateways;

use Razorpay\Api\Api;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayGateway implements PaymentGatewayInterface
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

        $razorpayData = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'razorpay');
        $key = $razorpayData['key'];
        $secret = $razorpayData['secret'];
        $api = new Api($key, $secret);

        $notify_url = $data['success_url'];
        $orderData = [
            'receipt'         => $data['payment_for'],
            'amount'          => intval($amount),
            'currency'        => 'INR',
            'payment_capture' => 1 // auto capture
        ];

        $razorpayOrder = $api->order->create($orderData);

        $razorpayData = [
            'key'               => $key,
            'amount'            => $orderData['amount'],
            'name'              => $orderData['receipt'],
            'description'       => $data['payment_for'] . ' via Razorpay Gateway',
            'prefill'           => [
                'name'              => $data['customer_name'],
                'email'             => $data['customer_email'],
                'contact'           => $data['customer_phone']
            ],
            'notes'             => [
                'merchant_order_id' => (string) Str::uuid(),
            ],
            'order_id'          => $razorpayOrder['id']
        ];

        $jsonData = json_encode($razorpayData);

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'Razorpay',
            'cancel_url' => $data['cancel_url'],
'gateway_type' => $data['gateway_type'],
            'razorpayOrderId' => $razorpayOrder['id'],
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

        $payment_method = 'razorpay';
        $booking_id = @$data['booking_id'];
        return view('front.booking-payment.razorpay', compact('jsonData', 'notify_url', 'booking_id', 'amount', 'payment_method'));
    }

    public function success(Request $request)
    {
        // get the information from session
        $paymentPayload = Session::get('paymentPayload');

        $razorpayData = PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'razorpay');
        $key = $razorpayData['key'];
        $secret = $razorpayData['secret'];
        $api = new Api($key, $secret);

        // get the information from the url, which has send by razorpay through post request
        $urlInfo = $request->all();

        // let, assume that the transaction was successfull
        $success = true;

        // Either razorpay_order_id or razorpay_subscription_id must be present
        // the keys of $attributes array must be follow razorpay convention
        try {
            $attributes = [
                'razorpay_order_id' => $paymentPayload['razorpayOrderId'],
                'razorpay_payment_id' => $urlInfo['razorpayPaymentId'],
                'razorpay_signature' => $urlInfo['razorpaySignature']
            ];

            $response = $api->utility->verifyPaymentSignature($attributes);
        } catch (SignatureVerificationError $e) {
            $success = false;
        }

        if ($success === true) {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($urlInfo['trxref']);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;

            return $paymentPayload;
        } else {
            return $paymentPayload['cancel_url'];
        }
    }

    public function cancel(Request $request) {}
}
