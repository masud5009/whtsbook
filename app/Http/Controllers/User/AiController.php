<?php

namespace App\Http\Controllers\User;

use App\Models\BasicSetting;
use Illuminate\Http\Request;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\DB;
use App\Models\AiTokenRecharge;
use App\Models\AiUsageToken;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Payment\PaymentHandler;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\Validator;

class AiController extends Controller
{
    public function buy(Request $request, PaymentService $paymentService)
    {
        $rules = [
            'payment_method' => 'required',
            'tokenamount' => 'required|min:1',
        ];

        if ($request->payment_method === 'stripe') {
            $rules['stripeToken'] = 'required';
        }

        if ($request->payment_method === 'iyzico') {
            $rules['identity_number'] = 'required';
            $rules['address'] = 'required';
            $rules['zip_code'] = 'required';
            $rules['country'] = 'required';
            $rules['city'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $price_per_token = (float) BasicSetting::getAiPricing()['current'];
        $bs = DB::table('basic_extendeds')->select('base_currency_text', 'base_currency_rate')->first();

        $amount = $request->tokenamount * $price_per_token;

        $gateway = strtolower(trim((string) $request->payment_method));
        $response = PaymentHandler::checkGateway($gateway, $amount, $bs);
        //currency check
        if ($response['status'] === 'error') {
            return response()->json([
                'status' => 'currency-error',
                'message' => $response['message'],
            ]);
        }

        $successUrl = match ($gateway) {
            'razorpay'  => route('tenant.razorpay.success'),
            'paytm'     => route('tenant.paytm.success'),
            'paytabs'   => route('tenant.paytabs.success'),
            'iyzico'    => route('tenant.iyzico.success'),
            default     => route('tenant.credit_buy.success') . '?gateway=' .  $response['gateway'],
        };
        $user = Auth::guard('web')->user();

        $data = [
            'payment_for' => 'Topup Credit',
            'amount' => $amount,
            'formatted_amount' => $response['amount'],
            'currency' => $response['currency'] ?? $bs->base_currency_text,
            'user_id' => $user->id,
            'customer_name' => $user->username,
            'customer_phone' => $user->phone,
            'customer_email' => $user->email,
            'gateway' => $gateway,
            'tokens' => $request->tokenamount,
            'is_admin' => 1, // 0 = user, 1 = admin (use for get paymentgateway info)
            //success and cancel url
            'success_url' => $successUrl,
            'cancel_url' => route('tenant.credit_buy.cancel') . '?gateway=' . $gateway,
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

        return redirect()->route('tenant.credit_buy.payment_success.view');
    }

    public function paymentCancel(Request $request, PaymentService $paymentService)
    {
        $gateway = $request->query('gateway');
        $paymentService->handleCancel($request, $gateway);

        return redirect()->route('tenant.credit_buy.payment_cancel.view');
    }

    /**
     * payment success and cancel view page display
     */
    public function viewsuccess()
    {
        return view('user.success');
    }
    public function viewcancel()
    {
        return view('user.cancel');
    }

    public function creditTopupHistory()
    {
        $user = Auth::guard('web')->user();
        $data['user'] = $user;
        $data['topups'] = AiTokenRecharge::where('user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->paginate(10);

        $aiUsage = AiUsageToken::where('user_id', $user->id)->first();
        $data['availableToken'] =
            (optional($aiUsage)->total_usable_tokens ?? 0)
            + (optional($aiUsage)->extend_token ?? 0)
            - (optional($aiUsage)->total_tokens ?? 0);
        $data['usedTokens'] = optional($aiUsage)->total_tokens ?? 0;

        $data['onlineGateways'] = PaymentGateway::where('status', 1)->get();
        $data['offlineGateways'] = OfflineGateway::where('status', 1)
            ->orderBy('serial_number')
            ->get();

        $stripeInfo = PaymentGateway::where('keyword', 'stripe')->value('information');
        $stripeInfo = $stripeInfo ? json_decode($stripeInfo, true) : null;
        $data['stripe_key'] = $stripeInfo['key'] ?? '';

        $authorizeInfo = PaymentGateway::where('keyword', 'authorize.net')->value('information');
        $authorizeInfo = $authorizeInfo ? json_decode($authorizeInfo, true) : null;
        $data['anetSrc'] = '';
        $data['authorizeClientKey'] = '';
        $data['authorizeLoginId'] = '';

        if ($authorizeInfo) {
            $data['anetSrc'] = $authorizeInfo['sandbox_check'] == 1
                ? 'https://jstest.authorize.net/v1/Accept.js'
                : 'https://js.authorize.net/v1/Accept.js';

            $data['authorizeClientKey'] = $authorizeInfo['public_key'] ?? '';
            $data['authorizeLoginId']   = $authorizeInfo['login_id'] ?? '';
        }

        $pricing = BasicSetting::getAiPricing();
        $data['price_per_token'] = $pricing['current'];
        $data['current_ai_provider'] = $pricing['provider'];
        $data['adminCurrency'] = PaymentHandler::getCurrencySettings(1, $user->id)->base_currency_text;

        return view('user.credit-topup-history', $data);
    }


    /**
     * razorpay notify function
     */
    public function razorpaySuccess(Request $request, PaymentService $paymentService)
    {
        $metadata = $paymentService->handleSuccess($request, 'razorpay');
        return redirect()->route('tenant.credit_buy.payment_success.view');
    }

    /**
     * paytm notify function
     */
    public function paytmSuccess(Request $request, PaymentService $paymentService)
    {
        if ($request->STATUS == 'TXN_SUCCESS') {
            $paymentService->handleSuccess($request, 'paytm');
            return redirect()->route('tenant.credit_buy.payment_success.view');
        }
        return redirect()->route('tenant.credit_buy.payment_cancel.view');
    }

    /**
     * paytabs notify function
     */
    public function paytabsSuccess(Request $request, PaymentService $paymentService)
    {
        if ($request['respStatus'] == "A" && $request['respMessage'] == 'Authorised') {
            $paymentService->handleSuccess($request, 'paytabs');
            return redirect()->route('tenant.credit_buy.payment_success.view');
        }
        return redirect()->route('tenant.credit_buy.payment_cancel.view');
    }

    /**
     * iyzico notify function
     */
    public function iyzicoSuccess(Request $request, PaymentService $paymentService)
    {
        $paymentService->handleSuccess($request, 'iyzico');
        return redirect()->route('tenant.credit_buy.payment_success.view');
    }
}
