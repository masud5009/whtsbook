<?php

namespace App\Services\Payment\Gateways;

use Cartalyst\Stripe\Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;
use Cartalyst\Stripe\Exception\CardErrorException;
use Cartalyst\Stripe\Exception\UnauthorizedException;

class StripeGateway implements PaymentGatewayInterface
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


        $stripeConf = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'stripe');

        Config::set('services.stripe.key', $stripeConf["key"]);
        Config::set('services.stripe.secret', $stripeConf["secret"]);

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'Stripe',
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

        try {
            // initialize stripe
            $stripe = Stripe::make(Config::get('services.stripe.secret'));
            try {

                if (!isset($data['stripeToken'])) {
                    return back()->with('error', __('Token Problem With Your Token.'));
                }

                // generate charge
                $charge = $stripe->charges()->create([
                    'source' => $data['stripeToken'],
                    'currency' => 'USD',
                    'amount'   => $data['formatted_amount'],
                ]);

                if ($charge['status'] == 'succeeded') {
                    return $data['success_url'];
                } else {
                    return $data['cancel_url'];
                }
            } catch (CardErrorException $e) {
                return $data['cancel_url'];
            }
        } catch (UnauthorizedException $e) {
            return $data['cancel_url'];
        }
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');
        $transaction_id = UserPermissionHelper::uniqidReal(8);
        $transaction_details = null;
        $paymentPayload['transaction_details'] = $transaction_details;
        $paymentPayload['transaction_id'] = $transaction_id;
        return $paymentPayload;
    }


    public function cancel(Request $request) {}
}
