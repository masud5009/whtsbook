<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\PaymentGateway\PayPalService;
use App\Services\Payment\PaymentGatewayInterface;


class PayPalGateway implements PaymentGatewayInterface
{
    private $payPalService;

    public function __construct()
    {
        $this->payPalService = new PayPalService();
    }

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

        $user_id = @$data['user_id'];
        $price = $data['formatted_amount'];

        $price = round($price, 2);
        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'PayPal',
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

        $response = $this->paypalProcessData($price, $success_url, $cancel_url, $user_id, $data['is_admin']);
        if (!empty($response) && isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {
                    return $link['href'];
                }
            }
        }
        return $cancel_url;
    }

    public function success(Request $request)
    {
        try {
            //get session
            $paymentPayload = Session::get('paymentPayload');

            $this->payPalService->setCredentials($paymentPayload['is_admin'], $paymentPayload['user_id']);
            $response = $this->payPalService->captureOrder($request['token']);

            if (isset($response['status']) && $response['status'] == 'COMPLETED') {

                $transaction_id = UserPermissionHelper::uniqidReal(8);
                $transaction_details = json_encode($response);

                $paymentPayload['transaction_details'] = $transaction_details;
                $paymentPayload['transaction_id'] = $transaction_id;


                return $paymentPayload;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    private function paypalProcessData($amount, $successUrl, $cancelUrl, $tenantId, $isAdmin)
    {
        try {
            $this->payPalService->setCredentials($isAdmin, $tenantId);
            $response = $this->payPalService->createOrder($amount, $successUrl, $cancelUrl);
            return $response;
        } catch (\Exception $e) {
            return redirect($cancelUrl)->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request) {}
}
