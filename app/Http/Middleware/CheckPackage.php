<?php

namespace App\Http\Middleware;

use App\Http\Helpers\UserPermissionHelper;
use Closure;
use Auth;

class CheckPackage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // if the admin is logged in & he has a role defined then this check will be applied
        if (Auth::check()) {
            $user = Auth::user();
            $package = UserPermissionHelper::currentPackage($user->id);
            if (empty($package)) {
                return redirect()->route('user-dashboard');
            }
        }
        return $next($request);
    }
}
