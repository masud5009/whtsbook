<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class PaytabsGateway implements PaymentGatewayInterface
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

        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];


        $paytabInfo = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'paytabs');

        if ($data['payment_for'] == 'Room Booking') {
            $paytab_currency = PaymentHandler::paytabInfo('user', $user_id);
        } else {
            $paytab_currency = PaymentHandler::paytabInfo('admin', null);
        }

        $description = $data['payment_for'] . ' via paytabs';
        try {
            $response = Http::withHeaders([
                'Authorization' => $paytabInfo['server_key'], // Server Key
                'Content-Type' => 'application/json',
            ])->post($paytabInfo['api_endpoint'], [
                'profile_id' => $paytabInfo['profile_id'], // Profile ID
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => uniqid(),
                'cart_description' => $description,
                'cart_currency' => $paytab_currency['currency'], // set currency by region
                'cart_amount' => $data['formatted_amount'],
                'return' => $success_url,
            ]);
            $responseData = $response->json();

            $paymentPayload = [
                'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
                'amount' => $data['amount'],
                'user_id' => @$data['user_id'],
                'booking_id' => @$data['booking_id'],
                'tokens' => @$data['tokens'],
                'payment_method' => 'Paytabs',
                'cancel_url' => $data['cancel_url'],
'gateway_type' => $data['gateway_type'],
                'package_id' => @$data['package_id'],
                'start_date' => @$data['start_date'],
                'expire_date' => @$data['expire_date'],
                'status' => @$data['status'],
                'transaction_id' => UserPermissionHelper::uniqidReal(8),
                'transaction_details' => json_encode($responseData),
            ];
            Session::put('metadata', $paymentPayload);
            // put some data in session before redirect to paytm url
            return $responseData['redirect_url'];
        } catch (\Exception $e) {
            return redirect($cancel_url);
        }
    }

    public function success(Request $request) {}

    public function cancel(Request $request) {}
}
