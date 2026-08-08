<?php
namespace App\Services\PaymentGateway;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalService extends PaymentService
{
    protected $provider;

    public function setCredentials($isAdmin = true, $tenantId = null)
    {
        $data = $this->getGatewayData('paypal', $isAdmin, $tenantId);
        if (! $data) {
            throw new \Exception('PayPal credentials not found.');
        }

        $payData = $data->convertAutoData();
        $mode    = $payData['sandbox_check'] == 1 ? 'sandbox' : 'live';

        config([
            'paypal.mode'                        => $mode,
            'paypal.' . $mode . '.client_id'     => $payData['client_id'],
            'paypal.' . $mode . '.client_secret' => $payData['client_secret'],
        ]);
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));

    }
    public function convertAmount($basicSettings, $amount)
    {
        if ($basicSettings->base_currency_text !== 'USD') {
            $rate   = floatval($basicSettings->base_currency_rate);
            $amount = round(($amount / $rate), 2);
        }
        return $amount;
    }

    public function createOrder($amount, $successUrl, $cancelUrl)
    {
        $this->provider->getAccessToken();

        return $this->provider->createOrder([
            "intent"              => "CAPTURE",
            "application_context" => [
                "return_url" => $successUrl,
                "cancel_url" => $cancelUrl,
            ],
            "purchase_units"      => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value"         => $amount,
                    ],
                ],
            ],
        ]);
    }

    public function captureOrder($token)
    {
        $this->provider->getAccessToken();
        return $this->provider->capturePaymentOrder($token);
    }

}
