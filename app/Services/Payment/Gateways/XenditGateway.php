<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class XenditGateway implements PaymentGatewayInterface
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

        $paydata =  PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'xendit');
        $currency_text = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id)->base_currency_text;

        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];

        try {
            $externalId = 'bk_' . $data['payment_for'] . '_' . Str::random(10);

            $authHeader = 'Basic ' . base64_encode($paydata['secret_key'] . ':');
            $res = Http::withHeaders([
                'Authorization' => $authHeader,
            ])
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => $externalId,
                    'amount' => $data['formatted_amount'],
                    'currency' => $currency_text,
                    'success_redirect_url' => $success_url,
                    'failure_redirect_url' => $cancel_url,
                ]);

            if (!$res->successful()) {
                return redirect($cancel_url);
            }

            $response = $res->json();

            Session::put('xendit_invoice_id', $response['id'] ?? null);
            Session::put('xendit_external_id', $externalId);

            $paymentPayload = [
                'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
                'amount' => $data['amount'],
                'user_id' => @$data['user_id'],
                'booking_id' => @$data['booking_id'],
                'tokens' => @$data['tokens'],
                'payment_method' => 'Xendit',
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

            return $response['invoice_url'];
        } catch (\Exception $e) {
            dd($e);
            return redirect($cancel_url);
        }
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');

        $paydata =  PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'xendit');
        $secret = $paydata['secret_key'] ?? null;

        $invoiceId = $request->get('invoice_id') ?: Session::get('xendit_invoice_id');

        // If invoice_id not present, try to validate via external_id (stored in session)
        $externalId = $request->get('external_id') ?: Session::get('xendit_external_id');


        $authHeader = 'Basic ' . base64_encode($secret . ':');

        try {
            // Prefer invoice_id endpoint if present
            if ($invoiceId) {
                $res = Http::withHeaders(['Authorization' => $authHeader])
                    ->get("https://api.xendit.co/v2/invoices/{$invoiceId}");
            } else {
                $res = Http::withHeaders(['Authorization' => $authHeader])
                    ->get('https://api.xendit.co/v2/invoices', ['external_id' => $externalId]);
            }

            if (!$res->successful()) {
                return false;
            }

            $invoice = $res->json();

            // If external_id search returns list, take first
            if (isset($invoice[0]) && is_array($invoice[0])) {
                $invoice = $invoice[0];
            }

            $status = strtoupper((string) ($invoice['status'] ?? ''));

            // Xendit invoice success statuses usually: PAID / SETTLED
            if (!in_array($status, ['PAID', 'SETTLED'], true)) {
                return false;
            }

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($res->json());

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;
            return $paymentPayload;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function cancel(Request $request) {}
}
