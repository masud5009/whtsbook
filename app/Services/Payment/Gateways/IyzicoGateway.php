<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\LimitCheckerHelper;
use App\Services\Payment\PaymentHandler;
use App\Http\Helpers\UserPermissionHelper;
use App\Services\Payment\PaymentGatewayInterface;

class IyzicoGateway implements PaymentGatewayInterface
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

        $paydata = PaymentHandler::getGatewayInfo($data['is_admin'], $user_id, 'iyzico');

        $_amount = $data['formatted_amount'];
        $success_url = $data['success_url'];
        $cancel_url = $data['cancel_url'];

        $first_name = $data['customer_name'];
        $last_name = $data['customer_name'];
        $email = $data['customer_email'];
        $phone = $data['customer_phone'];

        $city = $data['city'] ?? 'N/A';
        $country = $data['country'] ?? 'N/A';
        $address = $data['address'] ?? 'N/A';
        $zip_code = $data['zip_code'] ?? '12345';
        $identity_number = $data['identity_number'] ?? '00000000000';
        $basket_id = 'B' . uniqid(999, 99999);

        $options = new \Iyzipay\Options();
        $options->setApiKey($paydata['api_key']);
        $options->setSecretKey($paydata['secret_key']);
        if ($paydata['sandbox_status'] == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com"); // production mode
        }

        $conversion_id = uniqid(9999, 999999);
        # create request class
        $iyzipay_request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $iyzipay_request->setLocale(\Iyzipay\Model\Locale::EN);
        $iyzipay_request->setConversationId($conversion_id);
        $iyzipay_request->setPrice($_amount);
        $iyzipay_request->setPaidPrice($_amount);
        $iyzipay_request->setCurrency(\Iyzipay\Model\Currency::TL);
        $iyzipay_request->setBasketId($basket_id);
        $iyzipay_request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $iyzipay_request->setCallbackUrl($success_url);
        $iyzipay_request->setEnabledInstallments(array(2, 3, 6, 9));

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId(uniqid());
        $buyer->setName($first_name);
        $buyer->setSurname($last_name);
        $buyer->setGsmNumber($phone);
        $buyer->setEmail($email);
        $buyer->setIdentityNumber($identity_number);
        $buyer->setLastLoginDate("");
        $buyer->setRegistrationDate("");
        $buyer->setRegistrationAddress($address);
        $buyer->setIp("");
        $buyer->setCity($city);
        $buyer->setCountry($country);
        $buyer->setZipCode($zip_code);
        $iyzipay_request->setBuyer($buyer);

        $shippingAddress = new \Iyzipay\Model\Address();
        $shippingAddress->setContactName($first_name);
        $shippingAddress->setCity($city);
        $shippingAddress->setCountry($country);
        $shippingAddress->setAddress($address);
        $shippingAddress->setZipCode($zip_code);
        $iyzipay_request->setShippingAddress($shippingAddress);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($first_name);
        $billingAddress->setCity($city);
        $billingAddress->setCountry($country);
        $billingAddress->setAddress($address);
        $billingAddress->setZipCode($zip_code);
        $iyzipay_request->setBillingAddress($billingAddress);

        $q_id = uniqid(999, 99999);
        $basketItems = array();
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId($q_id);
        $firstBasketItem->setName("Purchase Id " . $q_id);
        $firstBasketItem->setCategory1("Product Purchase");
        $firstBasketItem->setCategory2("");
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
        $firstBasketItem->setPrice($_amount);
        $basketItems[0] = $firstBasketItem;

        $iyzipay_request->setBasketItems($basketItems);

        # make request
        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($iyzipay_request, $options);

        $paymentResponse = (array)$payWithIyzicoInitialize;
        foreach ($paymentResponse as $key => $paymentresponse) {
            $paymentInfo = json_decode($paymentresponse, true);
            if ($paymentInfo['status'] == 'success') {
                if (!empty($paymentInfo['payWithIyzicoPageUrl'])) {
                    Session::put('conversation_id', $conversion_id);
                    $paymentPayload = [
                        'payment_for' => $data['payment_for'],
'gateway_type' => $data['gateway_type'],
                        'amount' => $data['amount'],
                        'user_id' => @$data['user_id'],
                        'booking_id' => @$data['booking_id'],
                        'tokens' => @$data['tokens'],
                        'payment_method' => 'Iyzico',
                        'cancel_url' => $data['cancel_url'],
                        'gateway_type' => $data['gateway_type'],
                        'package_id' => @$data['package_id'],
                        'start_date' => @$data['start_date'],
                        'expire_date' => @$data['expire_date'],
                        'status' => @$data['status'],
                        'transaction_id' => UserPermissionHelper::uniqidReal(8),
                        'transaction_details' => json_encode($paymentInfo),
                    ];
                    Session::put('metadata', $paymentPayload);
                    return $paymentInfo['payWithIyzicoPageUrl'];
                }
            }
            return redirect($cancel_url);
        }
    }

    public function success(Request $request) {}

    public function cancel(Request $request) {}
}
