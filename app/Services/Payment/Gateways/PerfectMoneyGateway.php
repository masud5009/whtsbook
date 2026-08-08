<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class PerfectMoneyGateway implements PaymentGatewayInterface
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

        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'perfect_money');
        $currency = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id)->base_currency_text;

        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];

        $randomNo = substr(uniqid(), 0, 8);
        $val['PAYEE_ACCOUNT'] = $paydata['perfect_money_wallet_id'];;
        $val['PAYEE_NAME'] = "Admin";
        $val['PAYMENT_ID'] = "$randomNo"; //random id
        $val['PAYMENT_AMOUNT'] = $data['formatted_amount'];
        $val['PAYMENT_UNITS'] = "$currency";

        $val['STATUS_URL'] = $success_url;
        $val['PAYMENT_URL'] = $success_url;
        $val['PAYMENT_URL_METHOD'] = 'GET';
        $val['NOPAYMENT_URL'] = $cancel_url;
        $val['NOPAYMENT_URL_METHOD'] = 'GET';
        $val['SUGGESTED_MEMO'] = $data['customer_email'];
        $val['BAGGAGE_FIELDS'] = 'IDENT';

        $data['val'] = $val;
        $data['method'] = 'post';
        $data['url'] = 'https://perfectmoney.com/api/step1.asp';

        Session::put('payment_id', $randomNo);

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


        return view('payments.perfect-money', compact('data'));
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');
        $track = $request['PAYMENT_ID'];
        $id = Session::get('payment_id');

        $perfectMoneyInfo = PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'perfect_money');

        if ($request->PAYEE_ACCOUNT == $perfectMoneyInfo['perfect_money_wallet_id']  && $track == $id) {

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($request->all());

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;
            return  $paymentPayload;
        } else {
            return false;
        }
    }

    public function cancel(Request $request) {}
}
