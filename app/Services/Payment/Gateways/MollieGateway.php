<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class MollieGateway implements PaymentGatewayInterface
{
    public function  initialize(array $data)
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

        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'mollie');
        $bs = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id);
        Config::set('mollie.key', $paydata['key']);



        $notify_url = $data['success_url'];
        /**
         * we must send the correct number of decimals.
         * thus, we have used sprintf() function for format
         */
        $payment = Mollie::api()->payments->create([
            'amount' => [
                'currency' => $bs->base_currency_text,
                'value' => $amount
            ],
            'description' => "Room Booking",
            'redirectUrl' => $notify_url
        ]);

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'Mollie',
            'cancel_url' => $data['cancel_url'],
            'gateway_type' => $data['gateway_type'],
            'paymentId' => $payment->id,
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

        return $payment->getCheckoutUrl();
    }

    public function success(Request $request)
    {
        // get the information from session
        $paymentPayload = Session::get('paymentPayload');

        $paydata = PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'mollie');
        Config::set('mollie.key', $paydata['key']);

        $payment_info = Mollie::api()->payments->get($paymentPayload['paymentId']);

        if ($payment_info->isPaid() == true) {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($payment_info);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;
            return  $paymentPayload;
        } else {
            return $paymentPayload['cancel_url'];
        }
    }

    public function cancel(Request $request) {}
}
