<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class ToyyibpayGateway implements PaymentGatewayInterface
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
        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'toyyibpay');

        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];


        $customer_name = $data['customer_name'];
        $email = $data['customer_email'];
        $phone = $data['customer_phone'];

        $ref = uniqid();
        session()->put('toyyibpay_ref_id', $ref);
        $bill_description = $data['payment_for'] . ' via toyyibpay';

        $some_data = array(
            'userSecretKey' => $paydata['secret_key'],
            'categoryCode' => $paydata['category_code'],
            'billName' => $data['payment_for'],
            'billDescription' => $bill_description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $data['formatted_amount'],
            'billReturnUrl' => $success_url,
            'billExternalReferenceNo' => $ref,
            'billTo' => $customer_name,
            'billEmail' => $email,
            'billPhone' => $phone,
        );

        if ($paydata['sandbox_status'] == 1) {
            $host = 'https://dev.toyyibpay.com/'; // for development environment
        } else {
            $host = 'https://toyyibpay.com/'; // for production environment
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_URL, $host . 'index.php/api/createBill');  // sandbox will be dev.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $response = json_decode($result, true);

        if (!empty($response[0])) {
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

            return $host . $response[0]["BillCode"];
        } else {
            if (array_key_exists('msg', $response)) {
                session()->flash('error', $response['msg']);
            }
            return redirect($cancel_url);
        }
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');
        $ref = session()->get('toyyibpay_ref_id');
        if ($request['status_id'] == 1 && $request['order_id'] == $ref) {

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($ref);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;

            return $paymentPayload;
        } else {
            return $paymentPayload['cancel_url'];
        }
    }

    public function cancel(Request $request) {}
}
