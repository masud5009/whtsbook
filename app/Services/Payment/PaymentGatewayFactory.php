<?php

namespace App\Services\Payment;

use InvalidArgumentException;
use App\Services\Payment\Gateways\YocoGateway;
use App\Services\Payment\Gateways\PaytmGateway;
use App\Services\Payment\Gateways\IyzicoGateway;
use App\Services\Payment\Gateways\MollieGateway;
use App\Services\Payment\Gateways\PayPalGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\Gateways\XenditGateway;
use App\Services\Payment\Gateways\OfflineGateway;
use App\Services\Payment\Gateways\PaytabsGateway;
use App\Services\Payment\Gateways\PhonePeGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\Payment\Gateways\MidtransGateway;
use App\Services\Payment\Gateways\PaystackGateway;
use App\Services\Payment\Gateways\RazorpayGateway;
use App\Services\Payment\Gateways\InstamojoGateway;
use App\Services\Payment\Gateways\ToyyibpayGateway;
use App\Services\Payment\Gateways\MyfatoorahGateway;
use App\Services\Payment\Gateways\FlutterwaveGateway;
use App\Services\Payment\Gateways\MercadoPagoGateway;
use App\Services\Payment\Gateways\AuthorizenetGateway;
use App\Services\Payment\Gateways\PerfectMoneyGateway;

class PaymentGatewayFactory
{
    public static function make(string $gateway): PaymentGatewayInterface
    {
        $map = [
            'paypal'            => PayPalGateway::class,
            'razorpay'          => RazorpayGateway::class,
            'stripe'            => StripeGateway::class,
            'flutterwave'       => FlutterwaveGateway::class,
            'authorize.net'     => AuthorizenetGateway::class,
            'mollie'            => MollieGateway::class,
            'paystack'          => PaystackGateway::class,
            'instamojo'         => InstamojoGateway::class,
            'mercadopago'       => MercadoPagoGateway::class,
            'paytm'             => PaytmGateway::class,
            'yoco'              => YocoGateway::class,
            'xendit'            => XenditGateway::class,
            'toyyibpay'         => ToyyibpayGateway::class,
            'midtrans'          => MidtransGateway::class,
            'myfatoorah'        => MyfatoorahGateway::class,
            'paytabs'           => PaytabsGateway::class,
            'perfect_money'     => PerfectMoneyGateway::class,
            'phonepe'           => PhonePeGateway::class,
            'iyzico'            => IyzicoGateway::class,
            'offline'           => OfflineGateway::class
        ];
        if (! isset($map[$gateway])) {
            throw new InvalidArgumentException('Invalid payment gateway.');
        }
        return app($map[$gateway]);
    }
}
