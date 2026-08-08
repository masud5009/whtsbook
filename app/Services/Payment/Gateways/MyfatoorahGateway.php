<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Services\Payment\PaymentGatewayInterface;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;

class MyfatoorahGateway implements PaymentGatewayInterface
{
    private function mfConfig(array $paydata): array
    {
        return [
            'apiKey'      => $paydata['token'],
            'isTest'      => (int)($paydata['sandbox_status'] ?? 1) === 1,
            'countryCode' => $paydata['country_iso'] ?? 'KWT',
        ];
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

        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'myfatoorah');
        $currencyIso = PaymentHandler::getCurrencySettings($data['is_admin'], $user_id)->base_currency_text;

        // Unique order reference (CustomerReference)
        $orderId = 'BOOK-' . $data['user_id'] . '-' . Str::upper(Str::random(6));

        // Save session for success() verification
        Session::put('mf_order_id', $orderId);


        $callbackUrl = $data['success_url'];
        $errorUrl    = $data['cancel_url'];

        $mfObj = new MyFatoorahPayment($this->mfConfig($paydata));

        $payload = [
            'CustomerName'       => $data['customer_name'] ?: 'Guest',
            'InvoiceValue'       => $data['formatted_amount'],
            'DisplayCurrencyIso' => $currencyIso,
            'CustomerEmail'      => $data['customer_email'] ?: null,
            'CallBackUrl'        => $callbackUrl,
            'ErrorUrl'           => $errorUrl,

            'MobileCountryCode'  => $paydata['mobile_country_code'] ?? '+965',
            'CustomerMobile'     => $data['customer_phone'] ?: null,
            'Language'           => $paydata['language'] ?? 'en',
            'CustomerReference'  => $orderId,
            'UserDefinedField'   => null, // optional
            'InvoiceItems'       => [
                [
                    'ItemName'  => $data['payment_for'],
                    'Quantity'  => 1,
                    'UnitPrice' => $data['formatted_amount']
                ]
            ],
        ];

        // payment method id optionally pass
        $paymentMethodId = (int)($paydata['payment_method_id'] ?? 0);
        $sessionId = null;

        // getInvoiceURL returns invoiceURL
        $payment = $mfObj->getInvoiceURL($payload, $paymentMethodId, $orderId, $sessionId);

        if (!empty($payment['invoiceURL'])) {
            $paymentPayload = [
                'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
                'amount' => $data['amount'],
                'user_id' => @$data['user_id'],
                'booking_id' => @$data['booking_id'],
                'tokens' => @$data['tokens'],
                'payment_method' => 'MyFatoorah',
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
            return $payment['invoiceURL'];
        }

        return $errorUrl;
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');
        $sessionOrderId = Session::get('mf_order_id');

        $paymentId = $request->query('paymentId');
        if (!$paymentId) {
            return false;
        }

        $paydata =  PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'myfatoorah');

        $mfStatus = new MyFatoorahPaymentStatus($this->mfConfig($paydata));
        $data = $mfStatus->getPaymentStatus($paymentId, 'PaymentId');

        // Strong verification
        $invoiceStatus = $data->InvoiceStatus ?? null;   // Paid/Failed/Expired etc
        $customerRef   = $data->CustomerReference ?? null;

        if ($invoiceStatus === 'Paid' && $customerRef === $sessionOrderId) {
            $paymentPayload['transaction_id'] = $paymentId;
            $transaction_details = json_encode($data);

            $paymentPayload['transaction_details'] = $transaction_details;
            return $paymentPayload;
        }

        return $paymentPayload['cancel_url'];
    }

    public function cancel(Request $request) {}
}
