<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '*/user/room_booking/paytm/notify',
        '*/user/room_booking/flutterwave/notify',
        '*/user/room_booking/razorpay/notify',
        '*/user/room_booking/mollie/notify',
        '*/user/room_booking/mercadopago/notify',
        '*/user/room_booking/paytm/notify',
        '*/razorpay/booking/success?gateway=razorpay',
        'paytm/*',
        'paytabs/*',
        'iyzico/*',
        'user/paytm/*',
        'user/paytabs/*',
        'user/iyzico/*',
        'user/membership/paytm/*',
        'user/membership/paytabs/*',
        'user/membership/iyzico/*',

        '*/user/package_booking/paytm/notify',
        '*/user/package_booking/flutterwave/notify',
        '*/user/package_booking/razorpay/notify',
        '*/user/package_booking/mollie/notify',
        '*/user/package_booking/mercadopago/notify',
        '*/user/package_booking/paytm/notify',
        '/membership*',
        'whatsapp/webhook',
        '/whatsapp/webhook',
    ];
}
