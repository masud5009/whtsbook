<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Services\Payment\PaymentGatewayFactory;

class PaymentService
{
    /**
     * Initiate payment (no session store here).
     */
    public function pay(array $paymentData)
    {
        $gateway = $paymentData['gateway'];
        if ($paymentData['gateway_type'] == 'offline') {
            $gateway = 'offline';
        }
        $gatewayInstance = PaymentGatewayFactory::make($gateway);
        return $gatewayInstance->initialize($paymentData);
    }

    /**
     * Handle payment success callback.
     * Uses a unified metadata payload for booking/membership/topup actions.
     */
    public function handleSuccess(Request $request, ?string $gateway)
    {
        if (!$gateway) {
            $this->clearGatewayMetadata();
            return null;
        }

        $metadata = null;

        // Gateways that generate metadata from callback request
        if (!in_array($gateway, ['paytm', 'offline', 'paytabs', 'iyzico'], true)) {
            $gatewayInstance = PaymentGatewayFactory::make($gateway);
            $metadata = $gatewayInstance->success($request);

            if (!$metadata) {
                $this->clearGatewayMetadata();
                return null;
            }

            PaymentHandler::handleSuccess($metadata);
        }
        // Gateways that rely on session-stored metadata
        elseif (in_array($gateway, ['offline', 'paytabs', 'iyzico'], true)) {
            $metadata = Session::get('metadata');

            if (!$metadata) {
                $this->clearGatewayMetadata();
                return null;
            }

            PaymentHandler::handleSuccess($metadata);
        }
        // Paytm (or similar) sends metadata directly in request
        else {
            $metadata = $request->all();

            if (!$metadata) {
                $this->clearGatewayMetadata();
                return null;
            }

            PaymentHandler::handleSuccess($metadata);
        }

        $this->clearGatewayMetadata();
        return $metadata;
    }

    /**
     * Handle payment cancel callback.
     */
    public function handleCancel(Request $request, ?string $gateway): void
    {
        if (!$gateway) {
            $this->clearGatewayMetadata();
            return;
        }

        $gatewayInstance = PaymentGatewayFactory::make($gateway);
        $gatewayInstance->cancel($request);

        $this->clearGatewayMetadata();
    }

    /**
     * Clear gateway-related session metadata (used by offline/paytabs/iyzico).
     */
    protected function clearGatewayMetadata(): void
    {
        Session::forget('metadata');
    }
}
