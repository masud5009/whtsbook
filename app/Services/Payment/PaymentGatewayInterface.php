<?php

namespace App\Services\Payment;

use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initialize the payment (pass data whatever you need)
     */
    public function initialize(array $data);

    /**
     * Handle success callback
     */
    public function success(Request $request);

    /**
     * Handle failure or cancel
     */
    public function cancel(Request $request);
}
