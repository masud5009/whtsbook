<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Helpers\LimitCheckerHelper;

class LimitCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $feature, $method)
    {
        // if the admin is logged in & he has a role defined then this check will be applied
        if (Auth::guard('web')->check()) {

            $packageId = LimitCheckerHelper::getMembershipId(Auth::guard('web')->user()->id);
            $package = Package::find($packageId);
            $user = User::find(Auth::guard('web')->user()->id);

            if (empty($package)) {
                return redirect()->route('user-dashboard');
            }
            $userFeaturesCount = LimitCheckerHelper::userFeaturesCount($user->id);

            if ($method == 'store') {
                if ($feature == 'rooms') {

                    if ($package->room_limit > $userFeaturesCount['rooms'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {

                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }
                if ($feature == 'room_categories') {

                    if ($package->room_categories_limit > $userFeaturesCount['room_categories'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {

                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }
                if ($feature == 'room_bookings') {

                    if ($package->room_booking_limit > $userFeaturesCount['room_bookings'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }

                if ($feature == 'languages') {

                    if ($package->language_limit > $userFeaturesCount['languages'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }

                if ($feature == 'wp_numbers') {

                    if ($package->whatsapp_limit > $userFeaturesCount['wp_numbers'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }
            }

            if ($method == 'update') {

                if ($feature == 'rooms') {

                    if ($package->room_limit >= $userFeaturesCount['rooms'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }
                if ($feature == 'room_categories') {

                    if ($package->room_categories_limit >= $userFeaturesCount['room_categories'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {

                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }
                if ($feature == 'room_bookings') {
                    if ($package->room_booking_limit >= $userFeaturesCount['room_bookings'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }

                if ($feature == 'languages') {

                    if ($package->language_limit >= $userFeaturesCount['languages'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }

                if ($feature == 'wp_numbers') {

                    if ($package->whatsapp_limit >= $userFeaturesCount['wp_numbers'] && $this->checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)) {
                        session()->forget('CrossLimit');
                        return $next($request);
                    } else {
                        session()->put('CrossLimit', 'Reached max limit');
                        return response()->json('downgrade');
                    }
                }
            }
        }
        return $next($request);
    }

    private function checkFeaturesNotDowngraded($feature, $package, $userFeaturesCount)
    {
        $true = true;

        if ($feature != 'rooms') {

            if ($package->room_limit < $userFeaturesCount['rooms']) {
                return  $true = false;
            }
        }
        if ($feature != 'room_bookings') {

            if ($package->room_booking_limit < $userFeaturesCount['room_bookings']) {

                return  $true = false;
            }
        }

        if ($feature != 'languages') {

            if ($package->language_limit < $userFeaturesCount['languages']) {

                return $true = false;
            }
        }
        if ($feature != 'wp_numbers') {

            if ($package->whatsapp_limit < $userFeaturesCount['wp_numbers']) {

                return $true = false;
            }
        }
        return $true;
    }
}
