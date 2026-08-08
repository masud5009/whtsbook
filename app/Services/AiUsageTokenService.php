<?php

namespace App\Services;

use App\Models\User;
use App\Models\AiUsageToken;
use App\Models\BasicSetting;
use Illuminate\Mail\Message;
use App\Models\BasicExtended;
use App\Models\EmailTemplate;
use App\Models\AiTokenRecharge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Services\Payment\PaymentHandler;

class AiUsageTokenService
{
    /**
     * Consume AI tokens after a successful AI response.
     *
     * This method NEVER fails the request.
     * If user exceeds total allowed tokens (base + extend), the extra usage is stored as token_debt.
     * token_debt will be auto-adjusted on next recharge or membership credit.
     */
    public static function consumeAiTokens($userId,  $tokensToUse)
    {
        $tokensToUse = max(0, (int) $tokensToUse);

        return DB::transaction(function () use ($userId, $tokensToUse) {

            $row = AiUsageToken::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$row) return false;

            $used   = (int) $row->total_tokens;
            $base   = (int) $row->total_usable_tokens;
            $extend = (int) ($row->extend_token ?? 0);
            $debt   = (int) ($row->token_debt ?? 0);

            $limit     = $base + $extend;
            $usedAfter = $used + $tokensToUse;

            // Calculate overflow beyond allowed limit
            $overflow = max(0, $usedAfter - $limit);

            // Calculate how much extend_token should be consumed
            $extendUsedBefore = max($used - $base, 0);
            $extendUsedAfter  = max($usedAfter - $base, 0);
            $deductFromExtend = $extendUsedAfter - $extendUsedBefore;

            // Update total used tokens
            $row->total_tokens = $usedAfter;

            // Deduct from extend_token safely
            if ($deductFromExtend > 0) {
                $row->extend_token = max($extend - $deductFromExtend, 0);
            }

            // Store current overflow as debt
            $row->token_debt = $overflow;

            $row->save();
            return true;
        });
    }

    /**
     * Credit AI tokens from a membership purchase or extension.
     *
     * Membership tokens are added to BASE allowance (total_usable_tokens).
     * If token_debt exists, it is paid first.
     * Only remaining tokens (after debt clearance) are added to base.
     */
    public static function creditBaseTokensFromMembership($userId,  $tokens)
    {
        $tokens = max(0, (int)$tokens);

        DB::transaction(function () use ($userId, $tokens) {
            $row = AiUsageToken::where('user_id', $userId)->lockForUpdate()->first();

            if (!$row) {
                AiUsageToken::create([
                    'user_id' => $userId,
                    'total_tokens' => 0,
                    'total_usable_tokens' => $tokens,
                    'extend_token' => 0,
                    'token_debt' => 0,
                ]);
                return;
            }

            $debt = (int)($row->token_debt ?? 0);

            // Pay existing debt first
            $payDebt   = min($tokens, $debt);
            $debtAfter = $debt - $payDebt;

            // Remaining tokens go to base allowance
            $remain = $tokens - $payDebt;

            $row->token_debt = $debtAfter;
            $row->total_usable_tokens += $remain;
            $row->save();
        });
    }


    /**
     * Recharge AI tokens (top-up).
     * Recharge tokens are added to EXTEND allowance (extend_token).
     * If token_debt exists, it is cleared first.
     * Only remaining tokens (after debt clearance) increase extend_token.
     */
    public static function rechargeToken($userId, $topup, $metadata)
    {
        $userId = (int) $userId;
        $topup  = max(0, (int) $topup);

        //Create recharge record
        $currencySetting = PaymentHandler::getCurrencySettings($metadata['is_admin'], $userId);
        AiTokenRecharge::create([
            'user_id' => $userId,
            'token_amount' => $topup,
            'paid_amount' => $metadata['amount'],
            'status' => 'approved',
            'gateway_type' => 'online',
            'payment_method' => $metadata['payment_method'],
            'currency_text' => $currencySetting['base_currency_text'],
            'currency_text_position' => $currencySetting['base_currency_text_position'],
            'currency_symbol' => $currencySetting['base_currency_symbol'],
            'currency_symbol_position' => $currencySetting['base_currency_symbol_position'],
        ]);

        $row = AiUsageToken::where('user_id', $userId)
            ->lockForUpdate()
            ->first();

        // Create row if not exists
        if (!$row) {
            return AiUsageToken::create([
                'user_id'            => $userId,
                'total_tokens'       => 0,
                'total_usable_tokens' => 0,
                'extend_token'       => $topup,
                'token_debt'         => 0,
            ]);
        }

        $debt   = (int) ($row->token_debt ?? 0);
        $extend = (int) ($row->extend_token ?? 0);

        // Pay debt first
        $payDebt     = min($topup, $debt);
        $debtAfter   = $debt - $payDebt;

        // Remaining topup goes to extend_token
        $remainTopup = $topup - $payDebt;
        $extendAfter = $extend + $remainTopup;

        $row->token_debt   = $debtAfter;
        $row->extend_token = $extendAfter;
        $row->save();

        // Send email
        AiUsageTokenService::sendEmailToUser('token_purchase_success', $userId, $topup, $metadata['amount']);
        return $row;
    }


    /**
     * Send email notification to user after purchase ai credit
     */
    public static function sendEmailToUser($template_type, $userId,  $topup, $amount)
    {
        $user = User::find($userId);
        $temp = EmailTemplate::where('email_type', '=', $template_type)->first();
        $body = $temp->email_body;

        $be = BasicExtended::select(
            'is_smtp',
            'smtp_host',
            'smtp_port',
            'encryption',
            'smtp_username',
            'smtp_password',
            'from_mail',
            'to_mail',
            'from_name',
            'base_currency_text',
            'base_currency_text_position',
        )->first();

        $website_title = BasicSetting::value('website_title');
        $amount = currencyTextPrice($amount, $be->base_currency_text, $be->base_currency_text_position);
        $body = preg_replace("/{username}/", $user->username, $body);
        $body = preg_replace("/{token_amount}/", $topup, $body);
        $body = preg_replace("/{amount}/", $amount, $body);
        $body = preg_replace("/{website_title}/", $website_title, $body);

        //set smtp config
        if ($be->is_smtp == 1) {
            try {
                $smtp = [
                    'transport'  => 'smtp',
                    'host'       => $be->smtp_host,
                    'port'       => $be->smtp_port,
                    'encryption' => $be->encryption,
                    'username'   => $be->smtp_username,
                    'password'   => $be->smtp_password,
                    'timeout'    => null,
                    'auth_mode'  => null,
                ];
                Config::set('mail.mailers.smtp', $smtp);
            } catch (\Exception $e) {
                //
            }

            try {
                Mail::send([], [], function (Message $message) use ($be, $temp, $body, $user) {
                    $message->to($user->email)
                        ->from($be->from_mail, $be->from_name)
                        ->subject($temp->email_subject)
                        ->replyTo($be->to_mail, $be->from_name)
                        ->html($body, 'text/html');
                });
            } catch (\Exception $e) {
            }
        }
    }
}
