<?php

namespace App\Http\Controllers\Admin;

use App\Models\AiUsageToken;
use App\Models\BasicSetting;
use Illuminate\Http\Request;
use App\Models\AiTokenRecharge;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\AiUsageTokenService;
use Illuminate\Support\Facades\Session;
use App\Services\Payment\PaymentHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class AiCreditController extends Controller
{
    public function priceSettings()
    {
        $pricing = BasicSetting::getAiPricing();
        $data = [];
        $data['price_per_token'] = $pricing['current'];
        $data['gemini_price_per_token'] = $pricing['gemini'];
        $data['openai_price_per_token'] = $pricing['openai'];
        $data['current_ai_provider'] = $pricing['provider'];
        $data['base_currency_text'] = DB::table('basic_extendeds')->value('base_currency_text');
        return view('admin.ai-credit.price-settings', $data);
    }

    public function index(Request $request)
    {
        $data = [];
        $username = $request->username;
        $data['topups'] = AiTokenRecharge::with('user')
            ->when($username, function ($query, $username) {
                return $query->whereHas('user', function (Builder $builder) use ($username) {
                    $builder->where('username', 'like', '%' . $username . '%');
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);


        return view('admin.ai-credit.index', $data);
    }

    /**
     * Update price per token
     */
    public function creditPrice(Request $request)
    {
        $request->validate([
            'gemini_credit_price' => 'required|numeric|gt:0',
            'openai_credit_price' => 'required|numeric|gt:0',
        ]);

        $bss = BasicSetting::all();
        foreach ($bss as $bs) {
            $bs->gemini_price_per_token = $request->gemini_credit_price;
            $bs->openai_price_per_token = $request->openai_credit_price;
            $bs->price_per_token = ($bs->ai_name ?? 'gemini') === 'openai'
                ? $request->openai_credit_price
                : $request->gemini_credit_price;
            $bs->save();
        }
        return back()->with('success', __('Updated Successfully'));
    }

    /**
     * Recharge AI tokens (top-up).
     */
    public function recharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'tokens'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        $userId = $request->user_id;
        $tokens = (int) $request->tokens;

        $pricePerToken = (float) BasicSetting::getAiPricing()['current'];
        $amount = $tokens * $pricePerToken;

        $currencySetting = PaymentHandler::getCurrencySettings(1, $userId);

        AiTokenRecharge::create([
            'user_id' => $userId,
            'token_amount' => $tokens,
            'paid_amount' => $amount,
            'status' => 'approved',
            'gateway_type' => 'manual',
            'payment_method' => 'admin',
            'currency_text' => $currencySetting['base_currency_text'],
            'currency_text_position' => $currencySetting['base_currency_text_position'],
            'currency_symbol' => $currencySetting['base_currency_symbol'],
            'currency_symbol_position' => $currencySetting['base_currency_symbol_position'],
        ]);

        $usage = AiUsageToken::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_tokens' => 0,
                'total_usable_tokens' => 0,
                'extend_token' => 0,
                'token_debt' => 0,
            ]
        );

        $usage->increment('extend_token', $tokens);

        try {
            AiUsageTokenService::sendEmailToUser(
                'token_purchase_success',
                $userId,
                $tokens,
                $amount
            );
        } catch (\Throwable $e) {
            Session::flash('warning', __('Email could not be sent reason') . $e->getMessage());
        }

        Session::flash('success', __('Credit recharged successfully'));
        return "success";
    }

    /**
     * Update topup status
     */
    public function updateTopupStatus(Request $request)
    {
        $topup = AiTokenRecharge::with('user')->findOrFail($request->id);

        if ($topup->status !== 'pending') {
            return back()->with('warning', __('Only pending topups can be updated.'));
        }

        $topup->status = $request->status;
        $topup->save();

        $topupAmount = (int) $topup->token_amount;
        $userId = (int) $topup->user_id;

        if ($request->status === 'approved') {
            $row = AiUsageToken::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                AiUsageToken::create([
                    'user_id' => $userId,
                    'total_tokens' => 0,
                    'total_usable_tokens' => 0,
                    'extend_token' => $topupAmount,
                    'token_debt' => 0,
                ]);
            } else {
                $debt = (int) ($row->token_debt ?? 0);
                $extend = (int) ($row->extend_token ?? 0);

                $payDebt = min($topupAmount, $debt);
                $debtAfter = $debt - $payDebt;

                $remainTopup = $topupAmount - $payDebt;
                $extendAfter = $extend + $remainTopup;

                $row->token_debt = $debtAfter;
                $row->extend_token = $extendAfter;
                $row->save();
            }

            AiUsageTokenService::sendEmailToUser(
                'token_purchase_success',
                $userId,
                $topupAmount,
                $topup->paid_amount ?? 0
            );
        } else {
            AiUsageTokenService::sendEmailToUser(
                'token_purchase_rejected',
                $userId,
                $topupAmount,
                $topup->paid_amount ?? 0
            );
        }
        return back()->with('success', __('Status updated successfully.'));
    }
}
