<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Payment\PaymentHandler;
use App\Services\Payment\PaymentService;
use App\Http\Helpers\UserPermissionHelper;
use App\Http\Requests\Checkout\ExtendRequest;

class UserCheckoutController extends Controller
{
    public function checkout(ExtendRequest $request, PaymentService $paymentService)
    {
        $amount = $request->price;

        $bs = DB::table('basic_extendeds')->select('base_currency_text', 'base_currency_rate')->first();

        //for free subscription
        if ($request->package_type == "trial" || $request->price == 0) {
            $transaction_details = $request->package_type == "trial" ? "Trial" : "Free";
            $metadata = [
                'package_id' => $request->package_id,
                'user_id' => $request->user_id,
                'amount' => 0,
                'payment_method' => '-',
                'transaction_id' => UserPermissionHelper::uniqidReal(8),
                'transaction_details' => $transaction_details,
                'status' => 1,
                'start_date' => $request->start_date,
                'expire_date' => $request->expire_date,
            ];
            PaymentHandler::membershipExtend($metadata);
            return response()->json([
                'status' => 'success',
                'url' => route('trial-success.success.page'),
                'action' => 'redirect',
            ]);
        }

        //For paid subscription
        $gateway = strtolower(trim((string) $request->payment_method));
        $response = PaymentHandler::checkGateway($gateway, $amount, $bs);

        //currency check
        if ($response['status'] === 'error') {
            return response()->json([
                'status' => 'currency-error',
                'message' => $response['message'],
            ]);
        }

        $successUrl = match ($response['gateway']) {
            'razorpay'  => route('membership.razorpay.success'),
            'paytm'     => route('membership.paytm.success'),
            'paytabs'   => route('membership.paytabs.success'),
            'iyzico'    => route('membership.iyzico.success'),
            'offline'   => route('membership.offline.success'),
            default     => route('membership.success') . '?gateway=' .  $response['gateway'],
        };
        $user = Auth::guard('web')->user();

        $data = [
            'payment_for' => 'Extend Membership',
            'amount' => $amount,
            'formatted_amount' => $response['amount'],
            'currency' => $response['currency'] ?? $bs->base_currency_text,
            'user_id' => $user->id,
            'customer_name' => $user->username,
            'customer_phone' => $user->phone,
            'customer_email' => $user->email,
            'start_date' => $request->start_date,
            'expire_date' => $request->expire_date,
            'package_id' => $request->package_id,
            'status' => 1,
            'gateway' => $gateway,
            'is_admin' => 1, // 0 = user, 1 = admin (use for get paymentgateway info)
            //success and cancel url
            'success_url' => $successUrl,
            'cancel_url' => route('membership.cancel') . '?gateway=' . $gateway,
            //stripe
            'stripeToken' => $request->stripeToken ?? null,
            //authorize
            'opaqueDataValue' => $request->opaqueDataValue ?? null,
            'opaqueDataDescriptor' => $request->opaqueDataDescriptor ?? null,
            //offline gateway
            'gatewayId' => $response['gatewayType'] === 'offline' ? $request->payment_method : null,
            'gateway_type' => $response['gatewayType'],
            //iyzico
            'identity_number' => $request->identity_number ?? null,
            'address' => $request->address ?? null,
            'zip_code' => $request->zip_code ?? null,
            'country' => $request->country ?? null,
            'city' => $request->city ?? null,
        ];
        $link = $paymentService->pay($data);

        return normalizePaymentResponse($request, $link);
    }

    /**
     * payment success and cancel logic route
     */
    public function paymentSuccess(Request $request, PaymentService $paymentService)
    {
        $gateway = $request->query('gateway');
        $paymentService->handleSuccess($request, $gateway);

        return redirect()->route('success.page');
    }

    public function paymentCancel(Request $request, PaymentService $paymentService)
    {
        $gateway = $request->query('gateway');
        $paymentService->handleCancel($request, $gateway);

        return redirect()->route('cancel.page');
    }

    /**
     * razorpay notify function
     */
    public function razorpaySuccess(Request $request, PaymentService $paymentService)
    {
        $paymentService->handleSuccess($request, 'razorpay');
        return redirect()->route('success.page');
    }

    /**
     * paytm notify function
     */
    public function paytmSuccess(Request $request, PaymentService $paymentService)
    {
        if ($request->STATUS == 'TXN_SUCCESS') {
            $paymentService->handleSuccess($request, 'paytm');
            return redirect()->route('success.page');
        }
    }

    /**
     * paytabs notify function
     */
    public function paytabsSuccess(Request $request, PaymentService $paymentService)
    {
        if ($request['respStatus'] == "A" && $request['respMessage'] == 'Authorised') {
            $paymentService->handleSuccess($request, 'paytabs');
            return redirect()->route('success.page');
        }
    }

    /**
     * iyzico notify function
     */
    public function iyzicoSuccess(Request $request, PaymentService $paymentService)
    {
        $paymentService->handleSuccess($request, 'iyzico');
        return redirect()->route('success.page');
    }

    /**
     * iyzico notify function
     */
    public function offlineSuccess(Request $request, PaymentService $paymentService)
    {
        $paymentService->handleSuccess($request, 'offline');
        return redirect()->route('success.page');
    }
}
