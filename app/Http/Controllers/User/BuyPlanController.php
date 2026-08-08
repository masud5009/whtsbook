<?php

namespace App\Http\Controllers\User;

use App\Models\Package;
use App\Models\Membership;
use App\Models\BasicExtended;
use App\Models\User\Language;
use App\Models\OfflineGateway;
use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Helpers\UserPermissionHelper;

class BuyPlanController extends Controller
{
    public function index()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }
        $data['bex'] = $currentLang->basic_extended;

        $data['packages'] = Package::where('status', '1')->get();
        $nextPackageCount = Membership::query()->where([
            ['user_id', Auth::id()],
            ['expire_date', '>=', Carbon::now()->toDateString()]
        ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->count();
        //current package
        $data['current_membership'] = Membership::query()->where([
            ['user_id', Auth::id()],
            ['start_date', '<=', Carbon::now()->toDateString()],
            ['expire_date', '>=', Carbon::now()->toDateString()]
        ])->where('status', 1)->whereYear('start_date', '<>', '9999')->first();
        if ($data['current_membership']) {
            $countCurrMem = Membership::query()->where([
                ['user_id', Auth::id()],
                ['start_date', '<=', Carbon::now()->toDateString()],
                ['expire_date', '>=', Carbon::now()->toDateString()]
            ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999')->count();
            if ($countCurrMem > 1) {
                $data['next_membership'] = Membership::query()->where([
                    ['user_id', Auth::id()],
                    ['start_date', '<=', Carbon::now()->toDateString()],
                    ['expire_date', '>=', Carbon::now()->toDateString()]
                ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999')->orderBy('id', 'DESC')->first();
            } else {
                $data['next_membership'] = Membership::query()->where([
                    ['user_id', Auth::id()],
                    ['start_date', '>', $data['current_membership']->expire_date]
                ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->first();
            }
            $data['next_package'] = $data['next_membership'] ? Package::query()->where('id', $data['next_membership']->package_id)->first() : null;
        }
        $data['current_package'] = $data['current_membership'] ? Package::query()->where('id', $data['current_membership']->package_id)->first() : null;
        $data['package_count'] = $nextPackageCount;
        $be = BasicExtended::select('package_features')->firstOrFail();
        $allPfeatures = $be->package_features ? $be->package_features : "[]";
        $data['allPfeatures'] = json_decode($allPfeatures, true);
        return view('user.buy-plan.index', $data);
    }

    public function checkout($package_id)
    {
        $packageCount = Membership::query()->where([
            ['user_id', Auth::guard('web')->user()->id],
            ['expire_date', '>=', Carbon::now()->toDateString()]
        ])->whereYear('start_date', '<>', '9999')->where('status', '<>', 2)->count();

        $hasPendingMemb = UserPermissionHelper::hasPendingMembership(Auth::guard('web')->user()->id);
        if ($hasPendingMemb) {
            Session::flash('warning', __('You already have a Pending Membership Request.'));
            return back();
        }
        if ($packageCount >= 2) {
            Session::flash('warning', __('You have another package to activate after the current package expires. You cannot purchase / extend any package, until the next package is activated'));
            return back();
        }

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()
                ->get('lang'))
                ->first();
        } else {
            $currentLang = Language::where('is_default', 1)
                ->first();
        }

        $be = $currentLang->basic_extended;

        $data['package'] = Package::query()->findOrFail($package_id);
        $data['membership'] = Membership::query()->where([
            ['user_id', Auth::guard('web')->user()->id],
            ['expire_date', '>=', \Carbon\Carbon::now()->format('Y-m-d')]
        ])->where('status', '<>', 2)->whereYear('start_date', '<>', '9999')
            ->latest()
            ->first();
        $data['previousPackage'] = null;
        if (!is_null($data['membership'])) {
            $data['previousPackage'] = Package::query()
                ->where('id', $data['membership']->package_id)
                ->first();
        }
        $data['bex'] = $be;
        $data['user'] = Auth::guard('web')->user();


        //payment gateways
        $data['onlineGateways'] = PaymentGateway::where('status', 1)->get();
        $data['offlineGateways'] = OfflineGateway::where('status', 1)
            ->orderBy('serial_number')
            ->get();

        $stripeInfo = PaymentGateway::where('keyword', 'stripe')->value('information');
        $stripeInfo = $stripeInfo ? json_decode($stripeInfo, true) : null;
        $data['stripe_key'] = $stripeInfo['key'] ?? null;

        $authorizeInfo = PaymentGateway::where('keyword', 'authorize.net')->value('information');
        $authorizeInfo = $authorizeInfo ? json_decode($authorizeInfo, true) : null;

        if ($authorizeInfo) {
            $data['anetSrc'] = $authorizeInfo['sandbox_check'] == 1
                ? 'https://jstest.authorize.net/v1/Accept.js'
                : 'https://js.authorize.net/v1/Accept.js';

            $data['authorizeClientKey'] = $authorizeInfo['public_key'] ?? null;
            $data['authorizeLoginId']   = $authorizeInfo['login_id'] ?? null;
        }

        return view('user.buy-plan.checkout', $data);
    }
}
