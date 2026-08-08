<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class FlutterwaveGateway implements PaymentGatewayInterface
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

        $customer_email = $data['customer_email'];
        $customer_name = $data['customer_name'];
        $customer_phone = $data['customer_phone'];
        $amount  = $data['formatted_amount'];

        $flutterwaveData = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'flutterwave');
        $bs = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id);

        $secret_key = $flutterwaveData['secret_key'] ?? null;
        if (!$secret_key) {

            return redirect()->back()->with('error', __('Flutterwave secret key missing'));
        }

        $currency = strtoupper(trim((string) $bs->base_currency_text));
        if ($currency === '') {
            return redirect()->back()->with('error', 'Currency missing');
        }

        $notify_url = $data['success_url'];
        $tx_ref     = 'BOOK-' . $data['user_id'] . '-' . Str::upper(Str::random(6));
        // $tx_ref     = 'RB-' . $data['payment_for'] . '-' . time();

        $payload = [
            "tx_ref"       => $tx_ref,
            "amount"       => $amount,
            "currency"     => $currency,
            "redirect_url" => $notify_url,
            "customer" => [
                "email" => (string) $customer_email,
                "name"  => (string) $customer_name ?? 'Customer',
                "phonenumber" => (string) $customer_phone ?? '',
            ],
            "payment_options" => "card,banktransfer,ussd",
            "meta" => [
                // "booking_id" => (string) $data['booking_id'],
                "user_id"    => (string) $user_id,
            ],

            "customizations" => [
                "title"       => $data['payment_for'],
                "description" => "Payment " . $data['payment_for'],
            ],
        ];

        $curl = curl_init("https://api.flutterwave.com/v3/payments");

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$secret_key}",
                "Content-Type: application/json",
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 25,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $err = curl_error($curl);
            $no  = curl_errno($curl);
            curl_close($curl);
            return redirect()->back()->with('error', "CURL error ({$no}): {$err}");
        }

        curl_close($curl);

        $resp = json_decode($response, true);

        // session save
        $paymentPayload = [
            'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
            'amount' => $data['amount'],
            'user_id' => @$data['user_id'],
            'booking_id' => @$data['booking_id'],
            'tokens' => @$data['tokens'],
            'payment_method' => 'Flutterwave',
            'tx_ref' => $tx_ref,
            'secret_key' => $secret_key,
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

        return $resp['data']['link'] ?? null;;
    }


    public function success(Request $request)
    {
        $paymentPayload  = Session::get('paymentPayload');
        // Flutterwave redirect params
        $status         = $request->query('status');         // successful / cancelled / failed
        $returned_txref = $request->query('tx_ref');
        $transaction_id = $request->query('transaction_id');

        if (!$paymentPayload['tx_ref'] || !$paymentPayload['secret_key']) {
            return $paymentPayload['cancel_url'];
        }

        // must match tx_ref
        if (!$transaction_id || !$returned_txref || $returned_txref !== $paymentPayload['tx_ref']) {
            return $paymentPayload['cancel_url'];
        }

        if ($status !== 'successful') {
            return $paymentPayload['cancel_url'];
        }

        // Verify payment via v3 endpoint
        $verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify";
        $ch = curl_init($verifyUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer {$paymentPayload['secret_key']}",
                "Content-Type: application/json",
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 25,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode($response, true);
        // Check verification status
        $ok = (($resp['status'] ?? null) === 'success')
            && (($resp['data']['status'] ?? null) === 'successful')
            && (($resp['data']['tx_ref'] ?? null) === $paymentPayload['tx_ref']);

        if ($ok) {
            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($resp['trxref']);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;

            return $paymentPayload;
        }

        return false;
    }

    public function cancel(Request $request) {}
}
