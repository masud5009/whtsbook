<?php

namespace App\Services\Payment;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Package;
use App\Models\Membership;
use App\Models\BasicExtended;
use App\Models\AiTokenRecharge;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Helpers\MegaMailer;
use App\Models\User\RoomBooking;
use App\Models\User\BasicSetting;
use App\Services\TimzeZoneService;
use Illuminate\Support\Facades\DB;
use App\Models\User\PaymentGateway;
use App\Services\AiUsageTokenService;
use App\Models\User\BookingAdjustment;
use Illuminate\Support\Facades\Session;
use App\Services\BookingAdjustmentService;
use App\Jobs\SendRoomBookingNotificationToStaff;
use App\Models\OfflineGateway as AdminOfflineGateway;
use App\Models\PaymentGateway as adminPaymentGateway;
use App\Http\Controllers\Front\CheckoutController;
use App\Models\User\OfflineGateway as OfflineGatewayModel;

class PaymentHandler
{
    /**
     * Handle successful payment callback
     * Routes payment based on purpose (booking, membership, ai credit topup)
     */
    public static function handleSuccess($metadata)
    {
        return DB::transaction(function () use ($metadata) {

            $response = [];
            if ($metadata['payment_for'] == 'Room Booking') {
                $response = self::storeRoomBooking($metadata);
            } else if ($metadata['payment_for'] == 'Extend Membership') {
                $response = self::membershipExtend($metadata);
            } else if ($metadata['payment_for'] == 'Membership Buy') {
                $response = self::membershipBuy($metadata);
            } else {
                //Recharge AI tokens
                $userId = ($metadata['user_id'] ?? 0);
                $topup  = (int) ($metadata['tokens'] ?? 0);

                $currencySetting = self::getCurrencySettings($metadata['is_admin'], $userId);

                if ($metadata['gateway_type'] == 'offline') {
                    $gatewayName = self::getOfflineGatewayName($metadata['is_admin'], $userId, $metadata['gateway_id']);
                    AiTokenRecharge::create([
                        'user_id' => $userId,
                        'token_amount' => $topup,
                        'paid_amount' => $metadata['amount'],
                        'status' => 'pending',
                        'gateway_type' => 'offline',
                        'payment_method' => $gatewayName,
                        'currency_text' => $currencySetting['base_currency_text'],
                        'currency_text_position' => $currencySetting['base_currency_text_position'],
                        'currency_symbol' => $currencySetting['base_currency_symbol'],
                        'currency_symbol_position' => $currencySetting['base_currency_symbol_position'],
                    ]);
                    AiUsageTokenService::sendEmailToUser('token_purchase_pending', $userId, $topup, $metadata['amount']);
                } else {
                    AiUsageTokenService::rechargeToken($userId, $topup, $metadata);
                }
            }

            return $response;
        });
    }

    /**
     * Validate gateway currency/rules and calculate payable amount.
     */
    public static function checkGateway($gateway, $amount, $bs)
    {
        $currency = strtoupper($bs->base_currency_text);
        $rate     = $bs->base_currency_rate ?? 1;

        $error = function (string $key) {
            return ['status' => 'error', 'message' => $key];
        };

        $gatewayType = 'online';
        $gateway = $gateway;
        switch ($gateway) {

            case 'paypal':
                if ($currency === 'USD') {
                    $paidAmount = round($amount, 2);
                    if ($paidAmount < 1.00) {
                        return $error("Minimum amount for PayPal is 1.00 $currency");
                    }
                } else {
                    if ($rate <= 0) return $error('Invalid base currency rate.');
                    $paidAmount = round($amount / $rate, 2);
                }
                break;

            case 'paystack':
                if ($currency !== 'NGN') {
                    return $error('Invalid currency for paystack payment.');
                }
                $paidAmount = (int) round($amount * 100);
                break;

            case 'flutterwave':
                $allowedCurrencies = ['BIF', 'CAD', 'CDF', 'CVE', 'EUR', 'GBP', 'GHS', 'GMD', 'GNF', 'KES', 'LRD', 'MWK', 'MZN', 'NGN', 'RWF', 'SLL', 'STD', 'TZS', 'UGX', 'USD', 'XAF', 'XOF', 'ZMK', 'ZMW', 'ZWD'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for flutterwave payment.');
                }
                $paidAmount = (int) round($amount);
                break;

            case 'razorpay':
                if ($currency !== 'INR') {
                    return $error('Invalid currency for razorpay payment.');
                }
                $paidAmount = (int) round($amount * 100);
                break;

            case 'mercadopago':
                $allowedCurrencies = ['ARS', 'BOB', 'BRL', 'CLF', 'CLP', 'COP', 'CRC', 'CUC', 'CUP', 'DOP', 'EUR', 'GTQ', 'HNL', 'MXN', 'NIO', 'PAB', 'PEN', 'PYG', 'USD', 'UYU', 'VEF', 'VES'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for mercadopago payment.');
                }
                $paidAmount = round((float) $amount, 2);
                break;
            case 'perfect_money':
                $allowedCurrencies = ['USD'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for perfect_money payment.');
                }
                $paidAmount = $amount;
                break;

            case 'mollie':
                $allowedCurrencies = ['AED', 'AUD', 'BGN', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'ILS', 'ISK', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TWD', 'USD', 'ZAR'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for mollie payment.');
                }
                $paidAmount = sprintf('%0.2f', $amount);
                break;

            case 'stripe':
                if ($currency === 'USD') {
                    $paidAmount = round($amount, 2);
                } else {
                    if ($rate <= 0) return $error('Invalid base currency rate.');
                    $paidAmount = round($amount / $rate, 2);
                }

                // Minimum amount check for Stripe
                if ($paidAmount < 0.50) {
                    return $error("Minimum amount for Stripe is 0.50 $currency");
                }
                break;

            case 'authorize.net':
                $allowedCurrencies = ['USD', 'CAD', 'CHF', 'DKK', 'EUR', 'GBP', 'NOK', 'PLN', 'SEK', 'AUD', 'NZD'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for authorize.net payment.');
                }
                $paidAmount = number_format($amount, 2, '.', '');
                break;

            case 'phonepe':
                if ($currency !== 'INR') {
                    return $error('Invalid currency for phonepe payment.');
                }
                $paidAmount = intval($amount * 100);
                break;
            case 'iyzico':
                if ($currency !== 'TRY') {
                    return $error('Invalid currency for iyzico payment.');
                }
                $paidAmount = $amount;
                break;

            case 'myfatoorah':
                $allowedCurrencies = ['KWD', 'SAR', 'BHD', 'AED', 'QAR', 'OMR', 'JOD'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for myfatoorah payment.');
                }
                $paidAmount = (int) round($amount);
                break;

            case 'midtrans':
                if ($currency !== 'IDR') {
                    return $error('Invalid currency for midtrans payment.');
                }
                $paidAmount = (int)$amount * 1000;
                break;

            case 'toyyibpay':
                $allowedCurrencies = ['RM'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for toyyibpay payment.');
                }
                $paidAmount = (int) $amount * 100;
                break;

            case 'xendit':
                $allowedCurrencies = ['IDR', 'PHP', 'USD', 'SGD', 'MYR'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for xendit payment.');
                }
                $paidAmount = round($amount, 2);
                break;

            case 'paytm':
                $allowedCurrencies = ['INR'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for paytm payment.');
                }
                $paidAmount = $amount;
                break;
            case 'paytabs':
                $paytabs_info_user_type = isset($bs->user_id) ? 'user' : 'admin';
                $paytabInfo = self::paytabInfo($paytabs_info_user_type, $paytabs_info_user_type === 'user' ? $bs->user_id : null);
                if ($currency != $paytabInfo['currency']) {
                    return $error('Invalid currency for paytabs payment.');
                }
                $paidAmount = round($amount, 2);
                break;
            case 'instamojo':
                $allowedCurrencies = ['INR'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for instamojo payment.');
                }
                $paidAmount = (float)$amount;
                break;
            case 'yoco':
                $allowedCurrencies = ['ZAR'];
                if (!in_array($currency, $allowedCurrencies, true)) {
                    return $error('Invalid currency for yoco payment.');
                }
                $paidAmount = $amount * 100;
                break;

            default:
                $gatewayType = 'offline';
                $paidAmount = $amount;
                $gateway = 'offline';
                break;
        }
        return [
            'status' => 'success',
            'amount' => $paidAmount,
            'gatewayType' => $gatewayType,
            'gateway' => $gateway
        ];
    }

    /**
     * Paytab Information based on countery
     */
    public static function paytabInfo($type, $user_id = null)
    {
        if ($type == 'user') {
            $paytabs = PaymentGateway::where([['user_id', $user_id], ['keyword', 'paytabs']])->first();
        } else {
            $paytabs = adminPaymentGateway::where('keyword', 'paytabs')->first();
        }
        $paytabsInfo = json_decode($paytabs->information, true);
        if ($paytabsInfo['country'] == 'global') {
            $currency = 'USD';
        } elseif ($paytabsInfo['country'] == 'sa') {
            $currency = 'SAR';
        } elseif ($paytabsInfo['country'] == 'uae') {
            $currency = 'AED';
        } elseif ($paytabsInfo['country'] == 'egypt') {
            $currency = 'EGP';
        } elseif ($paytabsInfo['country'] == 'oman') {
            $currency = 'OMR';
        } elseif ($paytabsInfo['country'] == 'jordan') {
            $currency = 'JOD';
        } elseif ($paytabsInfo['country'] == 'iraq') {
            $currency = 'IQD';
        } else {
            $currency = 'USD';
        }
        return [
            'server_key' => $paytabsInfo['server_key'],
            'profile_id' => $paytabsInfo['profile_id'],
            'url'        => $paytabsInfo['api_endpoint'],
            'currency'   => $currency,
        ];
    }

    /**
     * Store Room Booking
     */
    public static function storeRoomBooking($metadata)
    {
        $booking = RoomBooking::findOrFail($metadata['booking_id']);

        $bookingAdjustment = BookingAdjustment::where('booking_id', $booking->id)->first();
        if ($bookingAdjustment && $bookingAdjustment->type == 'extra_payment') {
            BookingAdjustmentService::updateForExtraPayment($booking);
            $booking->payment_status = 1;
            $booking->save();
            return $booking;
        }

        $iyzico_payment_status = $metadata['payment_method'] == 'Iyzico' ? 1 : 0;
        //advance_payment_status => [1=>advance not paid,2=>advance paid]
        if ($booking->advance_amount > 0 && $booking->advance_payment_status == 1) { // advance payment
            $advance = $booking->advance_amount;
            $booking->paid_amount += $advance;
            $booking->due -= $advance;
            $booking->advance_amount = 0;
            $booking->payment_status = 2;
            $booking->advance_payment_status = 2;
            $booking->partial_amount = $advance;
        } else {
            $amount = $metadata['amount'];
            $booking->paid_amount += $amount;
            $booking->due -= $amount;
            $booking->payment_status = $metadata['gateway_type'] == 'offline' ? 4 : 1; // 4=>offline payment pending, 1=>paid
        }

        $booking->iyzico_payment_status = $iyzico_payment_status;
        $booking->conversation_id = $metadata['payment_method'] == 'Iyzico' ? Session::get('conversation_id') : null;
        $booking->gateway_type = $metadata['gateway_type'];
        $booking->payment_method = $metadata['payment_method'];
        $booking->save();

        SendRoomBookingNotificationToStaff::dispatch($booking->id);

        return $booking;
    }

    /**
     * Extend existing membership
     * Adds package AI tokens to base allowance (debt cleared first)
     */
    public static function membershipExtend($metadata)
    {
        $timezone = TimzeZoneService::getAdminTimeZone();
        $today = Carbon::now($timezone)->toDateString();
        $package = Package::find($metadata['package_id']);
        $user = User::query()->findOrFail($metadata['user_id']);
        $previousMembership = Membership::query()
            ->select('id', 'package_id', 'is_trial')
            ->where([
                ['user_id', $user->id],
                ['start_date', '<=', $today],
                ['expire_date', '>=', $today]
            ])
            ->where('status', 1)
            ->orderBy('created_at', 'DESC')
            ->first();
        if (!is_null($previousMembership)) {
            $previousPackage = Package::query()
                ->select('term')
                ->where('id', $previousMembership->package_id)
                ->first();

            if (($previousPackage->term === 'lifetime' || $previousMembership->is_trial == 1) && $metadata['transaction_details'] != '"offline"') {
                $membership = Membership::find($previousMembership->id);
                $membership->expire_date = Carbon::parse($metadata['start_date']);
                $membership->save();
            }
        }
        if ($user) {
            $bs = self::getCurrencySettings(1, $user->id);
            Membership::create([
                'price' => $metadata['amount'],
                'currency' => $bs->base_currency_text,
                'currency_symbol' => $bs->base_currency_symbol,
                'payment_method' => $metadata["payment_method"],
                'transaction_id' => $metadata['transaction_id'],
                'status' => $metadata["payment_method"] == 'Iyzico' ? 0 : $metadata["status"],
                'transaction_details' => $metadata['transaction_details'],
                'package_id' => $metadata['package_id'],
                'user_id' => $user->id,
                'start_date' => Carbon::parse($metadata['start_date']),
                'expire_date' => Carbon::parse($metadata['expire_date']),
                'is_trial' => 0,
                'trial_days' => 0,
                'conversation_id' => $metadata['payment_method'] == 'Iyzico' ? Session::get('conversation_id') : null
            ]);

            // update ai token
            AiUsageTokenService::creditBaseTokensFromMembership($user->id, (int)$package->total_ai_token);
        }

        $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
        $activation = Carbon::parse($lastMemb->start_date);
        $expire = Carbon::parse($lastMemb->expire_date);
        $file_name = self::membershipInvoice($metadata, "extend", $user, $metadata['amount'], $metadata["payment_method"], $user->phone, $bs->base_currency_symbol_position, $bs->base_currency_symbol, $bs->base_currency_text, $metadata['transaction_id'], $package->title, $lastMemb);

        $mailer = new MegaMailer();
        $data = [
            'toMail' => $user->email,
            'toName' => $user->fname,
            'username' => $user->username,
            'package_title' => $package->title,
            'package_price' => ($bs->base_currency_text_position == 'left' ? $bs->base_currency_text . ' ' : '') . $package->price . ($bs->base_currency_text_position == 'right' ? ' ' . $bs->base_currency_text : ''),
            'activation_date' => $activation->toFormattedDateString(),
            'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
            'membership_invoice' => $file_name,
            'website_title' => $bs->website_title,
            'templateType' => 'membership_extend',
            'type' => 'membershipExtend'
        ];
        $mailer->mailFromAdmin($data);

        return $user;
    }

    /**
     * Buy new membership
     * Creates user + membership and sends invoice email
     */
    public static function membershipBuy($metadata)
    {
        $package = Package::find($metadata['package_id']);
        $bs = self::getCurrencySettings(1, null);

        $amount = $metadata['amount'];
        $password = $metadata['password'];
        $checkout = new CheckoutController();
        $user = $checkout->store($metadata, $metadata['transaction_id'], $metadata['transaction_details'], $amount, $bs, $password);

        $lastMemb = $user->memberships()->orderBy('id', 'DESC')->first();
        $activation = Carbon::parse($lastMemb->start_date);
        $expire = Carbon::parse($lastMemb->expire_date);
        $file_name = self::membershipInvoice($metadata, "membership", $user, $amount, $metadata['payment_method'], $metadata['phone'], $bs->base_currency_symbol_position, $bs->base_currency_symbol, $bs->base_currency_text, $metadata['transaction_id'], $package->title, $lastMemb);

        if ($metadata['package_type'] == 'trial') {
            $templateType = 'registration_with_trial_package';
            $mailType = 'registrationWithTrialPackage';
        } elseif ($metadata['amount'] == 0) {
            $templateType = 'registration_with_free_package';
            $mailType = 'registrationWithFreePackage';
        } else {
            $templateType = 'registration_with_premium_package';
            $mailType = 'registrationWithPremiumPackage';
        }
        $mailer = new MegaMailer();
        $data = [
            'toMail' => $user->email,
            'toName' => $user->first_name . ' ' . $user->last_name,
            'username' => $user->username,
            'package_title' => $package->title,
            'package_price' => ($bs->base_currency_text_position == 'left' ? $bs->base_currency_text . ' ' : '') . $package->price . ($bs->base_currency_text_position == 'right' ? ' ' . $bs->base_currency_text : ''),
            'discount' => ($bs->base_currency_text_position == 'left' ? $bs->base_currency_text . ' ' : '') . $lastMemb->discount . ($bs->base_currency_text_position == 'right' ? ' ' . $bs->base_currency_text : ''),
            'total' => ($bs->base_currency_text_position == 'left' ? $bs->base_currency_text . ' ' : '') . $lastMemb->price . ($bs->base_currency_text_position == 'right' ? ' ' . $bs->base_currency_text : ''),
            'activation_date' => $activation->toFormattedDateString(),
            'expire_date' => Carbon::parse($expire->toFormattedDateString())->format('Y') == '9999' ? 'Lifetime' : $expire->toFormattedDateString(),
            'membership_invoice' => $file_name,
            'website_title' => $bs->website_title,
            'templateType' => $templateType,
            'type' => $mailType
        ];
        $mailer->mailFromAdmin($data);
    }

    /**
     * Get payment gateway configuration (admin or user)
     * @params $is_admin (1 for admin, 0 for user)
     */
    public static function getGatewayInfo($is_admin, $user_id, $gatewayKeyword = null)
    {
        $gateway = $is_admin == 1
            ? adminPaymentGateway::whereKeyword($gatewayKeyword)->first()
            : PaymentGateway::whereKeyword($gatewayKeyword)
            ->where('user_id', $user_id)
            ->first();

        if (!$gateway) {
            return null;
        }

        return json_decode($gateway->information, true);
    }

    /**
     * Fetch base currency information
     */
    public static function getCurrencySettings($is_admin, $user_id)
    {
        $basicInfo = $is_admin == 1
            ? BasicExtended::select(
                'base_currency_text',
                'base_currency_symbol',
                'base_currency_symbol_position',
                'base_currency_text_position'
            )->first()
            : BasicSetting::where('user_id', $user_id)->select(
                'base_currency_text',
                'base_currency_symbol',
                'base_currency_symbol_position',
                'base_currency_text_position'
            )->firstOrFail();
        return $basicInfo;
    }

    /**
     * Fetch offline payment gateway name based on admin/user
     */
    public static function getOfflineGatewayName($is_admin, $user_id, $gatewayId)
    {
        if ($is_admin == 1) {
            $payment_method =  AdminOfflineGateway::query()
                ->where('id', $gatewayId)
                ->where('status', '=', 1)
                ->value('name');
        } else {
            $payment_method =  OfflineGatewayModel::query()
                ->where('user_id', $user_id)
                ->where('id', $gatewayId)
                ->where('status', '=', 1)
                ->value('name');
        }

        return $payment_method;
    }

    /**
     * Generate membership invoice PDF
     */
    public static function membershipInvoice($request, $key, $member, $amount, $payment_method, $phone, $base_currency_symbol_position, $base_currency_symbol, $base_currency_text, $order_id, $package_title, $membership)
    {
        $file_name = uniqid($key) . ".pdf";
        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'logOutputFile' => storage_path('logs/log.htm'),
            'tempDir' => storage_path('logs/')
        ])->loadView('pdf.membership', compact('request', 'member', 'amount', 'payment_method', 'phone', 'base_currency_symbol_position', 'base_currency_symbol', 'base_currency_text', 'order_id', 'package_title', 'membership'));
        $output = $pdf->output();
        @mkdir(public_path('assets/front/invoices/'), 0775, true);
        file_put_contents(public_path('assets/front/invoices/' . $file_name), $output);
        return $file_name;
    }
}
