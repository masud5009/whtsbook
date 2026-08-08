<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class MercadoPagoGateway implements PaymentGatewayInterface
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

        $mercadopagoData = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'mercadopago');
        $token = $mercadopagoData['token'];
        $sandbox_status = $mercadopagoData['sandbox_check'];

        $bs = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id);

        $notify_url = $data['success_url'];
        $complete_url = route('room_booking.success');
        $cancel_url = route('room_booking.cancel');

        $curl = curl_init();

        $preferenceData = [
            'items' => [
                [
                    'id' => uniqid(),
                    'title' => $data['payment_for'],
                    'description' => $data['payment_for'] . ' Using MercadoPago Gateway',
                    'quantity' => 1,
                    'currency_id' => $bs->base_currency_text,
                    'unit_price' => $amount,
                ]
            ],
            'payer' => [
                'email' => $data['customer_email']
            ],
            'back_urls' => [
                'success' => $complete_url,
                'pending' => '',
                'failure' => $cancel_url
            ],
            'notification_url' => $notify_url,
            'auto_return' => 'approved'
        ];

        $httpHeader = ['Content-Type: application/json'];

        $url = 'https://api.mercadopago.com/checkout/preferences?access_token=' . $token;

        $curlOPT = [
            CURLOPT_URL             => $url,
            CURLOPT_CUSTOMREQUEST   => 'POST',
            CURLOPT_POSTFIELDS      => json_encode($preferenceData, true),
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_HTTPHEADER      => $httpHeader
        ];

        curl_setopt_array($curl, $curlOPT);

        $response = curl_exec($curl);
        $responseInfo = json_decode($response, true);

        curl_close($curl);

        if (!isset($responseInfo['init_point']) && !isset($responseInfo['sandbox_init_point'])) {
            $errorMessage = $responseInfo['message'] ?? __('Unable to start payment.');
            return redirect()->back()->with('error', $errorMessage);
        }

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'MercadoPago',
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

        if ($sandbox_status == 1) {
            return $responseInfo['sandbox_init_point'];
        } else {
            return $responseInfo['init_point'];
        }
    }

    public function success(Request $request)
    {
        // get the information from session
        $paymentPayload = Session::get('paymentPayload');

        if ($request['status'] == 'approved') {

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($request->all());

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;


            return $paymentPayload;
        } else {
            return  $paymentPayload['cancel_url'];
        }
    }

    public function curlCalls($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $curlData = curl_exec($curl);
        curl_close($curl);
        return $curlData;
    }

    public function cancel(Request $request) {}
}
