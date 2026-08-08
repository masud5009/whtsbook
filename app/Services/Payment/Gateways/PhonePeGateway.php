<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class PhonePeGateway implements PaymentGatewayInterface
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

        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];
        $_amount = $data['formatted_amount'];

        $paydata =  PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'phonepe');
        $sandboxCheck = $paydata['sandbox_status'];
        $clientId = $paydata['merchant_id'];
        $clientSecret = $paydata['salt_key'];

        //* Here i completed 1 step which is generating access token in each request
        $accessToken = $this->getPhonePeAccessToken($clientId, $clientSecret, $sandboxCheck);

        if (!$accessToken) {
            return back()->withError(__('Failed to get PhonePe access token') . '.');
        }
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
        return $this->initiatePayment($accessToken, $success_url, $cancel_url, $_amount, $sandboxCheck);
    }

    /**
     * Initiate PhonePe payment
     */
    public function initiatePayment($accessToken, $successUrl, $cancelUrl, $_amount, $sandboxCheck)
    {
        $baseUrl = $sandboxCheck
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay'
            : 'https://api.phonepe.com/apis/pg/checkout/v2/pay';

        // Generate a unique merchantOrderId and store it in the session
        $merchantOrderId = uniqid();
        Session::put('merchantOrderId', $merchantOrderId);

        //here we preapare the parameter of the request
        $payload = [
            'merchantOrderId' => $merchantOrderId,
            'amount' => $_amount,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'merchantUrls' => [
                    'redirectUrl' => $successUrl,
                    'cancelUrl' => $cancelUrl,
                ],
            ],
        ];

        try {
            //after preparing the parameter we send a request to create a payment link
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $accessToken,
            ])->post($baseUrl, $payload);
            $responseData = $response->json();

            //after successfully created the payment link of we redirect the user to api responsed redirectUrl
            if ($response->successful() && isset($responseData['redirectUrl'])) {
                return redirect()->away($responseData['redirectUrl']);
            } else {
                // Handle API errors
                Session::forget(['merchantOrderId']);
                return back()->with('error', 'Failed to initiate payment: ' . ($responseData['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Session::forget(['merchantOrderId']);
            return response()->json([
                'success' => false,
                'code' => 'NETWORK_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PhonePe access token
     */
    private function getPhonePeAccessToken($clientId, $clientSecret, $sandboxCheck)
    {
        return Cache::remember('phonepe_access_token', 3500, function () use ($clientId, $clientSecret, $sandboxCheck) {
            $tokenUrl = $sandboxCheck
                ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
                : 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';


            $response = Http::asForm()->post($tokenUrl, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'client_version' => 1,
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }
            return null;
        });
    }

    /**
     * Verify Order Status
     */
    private function verifyOrderStatus($merchantOrderId, $clientId, $clientSecret, $sandboxCheck)
    {
        try {
            $accessToken = $this->getPhonePeAccessToken($clientId, $clientSecret, $sandboxCheck);

            if (!$accessToken) {
                throw new \Exception('Failed to get access token');
            }

            $baseUrl = $sandboxCheck
                ? "https://api-preprod.phonepe.com/apis/pg-sandbox/payments/v2/order/{$merchantOrderId}/status"
                : "https://api.phonepe.com/apis/pg/payments/v2/order/{$merchantOrderId}/status";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $accessToken,
            ])->get($baseUrl);

            $responseData = $response->json();
            if ($response->successful() && isset($responseData['state'])) {
                return [
                    'success' => true,
                    'state' => $responseData['state'] ?? null,
                    'amount' => $responseData['amount'] ?? null,
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json() ?? 'Unknown error'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function success(Request $request)
    {
        $paymentPayload = Session::get('paymentPayload');

        $paydata = PaymentHandler::getGatewayInfo($paymentPayload['is_admin'], $paymentPayload['user_id'], 'phonepe');
        $sandboxCheck = $paydata['sandbox_status'];
        $clientId = $paydata['merchant_id'];
        $clientSecret = $paydata['salt_key'];

        $merchantOrderId = $request->input('merchantOrderId') ?? (Session::get('merchantOrderId') ?? uniqid());
        $verificationResponse = $this->verifyOrderStatus($merchantOrderId, $clientId, $clientSecret, $sandboxCheck);

        if ($verificationResponse['success']) {

            $transaction_id = UserPermissionHelper::uniqidReal(8);
            $transaction_details = json_encode($verificationResponse);

            $paymentPayload['transaction_details'] = $transaction_details;
            $paymentPayload['transaction_id'] = $transaction_id;

            return $paymentPayload;
        } else {
            return false;
        }
    }

    public function cancel(Request $request) {}
}
