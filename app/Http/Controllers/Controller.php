<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User\Language;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User\BasicSetting;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use App\Services\Payment\OfflinePaymentInstruction;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getUserCurrentLanguage($userId)
    {
        // get the current locale of this system
        if (Session::has('user_lang')) {
            $locale = Session::get('user_lang');
        }
        if (empty($locale)) {
            $language = Language::query()->where('is_default', 1)->where('user_id', $userId)->firstOrFail();
        } else {
            $language = Language::query()->where('code', $locale)->where('user_id', $userId)->firstOrFail();
        }
        return $language;
    }


    public function getUserCurrencyInfo($userId)
    {
        return BasicSetting::query()
            ->where('user_id', $userId)
            ->select(
                'base_currency_symbol',
                'base_currency_symbol_position',
                'base_currency_text',
                'base_currency_text_position',
                'base_currency_rate'
            )->first();
    }

    public function getUserBreadcrumb($userId)
    {
        return BasicSetting::query()->where('user_id', $userId)->select('breadcrumb')->first();
    }


    public function makeInvoice($request, $key, $member, $password, $amount, $payment_method, $phone, $base_currency_symbol_position, $base_currency_symbol, $base_currency_text, $order_id, $package_title, $membership)
    {
        $file_name = uniqid($key) . ".pdf";
        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'logOutputFile' => storage_path('logs/log.htm'),
            'tempDir' => storage_path('logs/')
        ])->loadView('pdf.membership', compact('request', 'member', 'password', 'amount', 'payment_method', 'phone', 'base_currency_symbol_position', 'base_currency_symbol', 'base_currency_text', 'order_id', 'package_title', 'membership'));
        $output = $pdf->output();
        @mkdir(public_path('assets/front/invoices/'), 0775, true);
        file_put_contents(public_path('assets/front/invoices/' . $file_name), $output);
        return $file_name;
    }

    public function changeLanguage(Request $request)
    {
        // put the selected language in session
        $langCode = $request['lang_code'];

        $request->session()->put('currentLocaleCode', $langCode);

        return redirect()->back();
    }
    public function tax($amount, $per)
    {
        $tax = ($amount * $per) / 100;
        return round($tax, 2);
    }



    /**
     * Offline Payment Instruction (Admin,Tenant)
     */
    public function getPaymentInstructions(Request $request)
    {
        return OfflinePaymentInstruction::getInstruction($request);
    }
}
