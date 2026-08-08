<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Http\Helpers\UserPermissionHelper;
use App\Models\OfflineGateway as AdminOfflineGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Models\User\OfflineGateway as OfflineGatewayModel;

class OfflineGateway implements PaymentGatewayInterface
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

        if ($data['is_admin'] == 1) {
            $payment_method =  AdminOfflineGateway::query()
                ->where('id', $data['gatewayId'])
                ->where('status', '=', 1)
                ->value('name');
        } else {
            $payment_method =  OfflineGatewayModel::query()
                ->where('user_id', $user_id)
                ->where('id', $data['gatewayId'])
                ->where('status', '=', 1)
                ->value('name');
        }

        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => $payment_method,
            'cancel_url' => $data['cancel_url'],
            'gateway_type' => 'offline',
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
            'transaction_id' => UserPermissionHelper::uniqidReal(8),
            'transaction_details' => 'offline',
            'gateway_id' => $data['gatewayId'],
            'is_admin' => $data['is_admin'],
        ];
        Session::put('metadata', $paymentPayload);
        return $data['success_url'];
    }
    public function success(Request $request) {}
    public function cancel(Request $request) {}
}
