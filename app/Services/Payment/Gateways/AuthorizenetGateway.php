<?php

namespace App\Services\Payment\Gateways;

use Omnipay\Omnipay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class AuthorizenetGateway implements PaymentGatewayInterface
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

        $authorizeInfo = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'authorize.net');
        $bs = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id);

        $gateway    = Omnipay::create('AuthorizeNetApi_Api');
        $gateway->setAuthName($authorizeInfo['login_id']);
        $gateway->setTransactionKey($authorizeInfo['transaction_key']);
        $gateway->setTransactionKey($authorizeInfo['transaction_key']);

        if ($authorizeInfo['sandbox_check'] == 1) {
            $gateway->setTestMode(true);
        }

        $opaqueDataDescriptor = $data['opaqueDataDescriptor'];
        $opaqueDataValue = $data['opaqueDataValue'];

        if ($opaqueDataDescriptor && $opaqueDataValue) {
            try {
                // Generate a unique merchant site transaction ID.
                $transactionId = rand(100000000, 999999999);
                $response      = $gateway->authorize([
                    'amount'               => $amount,
                    'currency'             => $bs->base_currency_text,
                    'transactionId'        => $transactionId,
                    'opaqueDataDescriptor' => $opaqueDataDescriptor,
                    'opaqueDataValue'      => $opaqueDataValue,
                ])->send();
            } catch (\Exception $e) {
                return $data['cancel_url'];
            }

            if ($response->isSuccessful()) {

                // Captured from the authorization response.
                $transactionReference = $response->getTransactionReference();

                $response = $gateway->capture([
                    'amount'               => intval($amount),
                    'currency'             => $bs->base_currency_text,
                    'transactionReference' => $transactionReference,
                ])->send();

                $paymentPayload = [
                    'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
                    'amount' => $data['amount'],
                    'user_id' => @$data['user_id'],
                    'booking_id' => @$data['booking_id'],
                    'tokens' => @$data['tokens'],
                    'payment_method' => 'Authorizenet',
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
                ];
                Session::put('paymentPayload', $paymentPayload);

                return $data['success_url'];
            } else {
                return $data['cancel_url'];
            }
        }
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');
        $transaction_id = UserPermissionHelper::uniqidReal(8);
        $transaction_details = null;

        $paymentPayload['transaction_details'] = $transaction_details;
        $paymentPayload['transaction_id'] = $transaction_id;

        return  $paymentPayload;
    }

    public function cancel(Request $request) {}
}
