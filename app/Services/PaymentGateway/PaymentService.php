<?php
namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use App\Models\User\PaymentGateway as UserPaymentGateway;

abstract class PaymentService {
  /**
   * It's must be implemented
   * @param $isAdmin
   * @param $tenantId
   */
  public abstract function setCredentials($isAdmin = true, $tenantId = null);

  /**
   * @param $isAdmin
   * @param $tenantId
   * @param $keyword
   */
  protected function getGatewayData($keyword, $isAdmin, $tenantId)
  {
    return $isAdmin
      ? PaymentGateway::whereKeyword($keyword)->first()
      : UserPaymentGateway::where('user_id', $tenantId)->whereKeyword($keyword)->first();
  }

  /**
   * @param $base_currency_text >> It's website currency
   * @param $paymentCurreny >> which currency accecpt paypent gateway
   */
  public function checkCurrency(string $base_currency_text, array $paymentCurreny)
  {
    return in_array($base_currency_text,  $paymentCurreny);
  }



}
