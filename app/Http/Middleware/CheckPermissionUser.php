<?php

namespace App\Http\Middleware;

use App\Http\Helpers\UserPermissionHelper;
use Closure;
use Illuminate\Support\Facades\Auth;


class CheckPermissionUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        // if the admin is logged in & he has a role defined then this check will be applied
        if (Auth::check()) {
            $user = Auth::user();
            $permissions = UserPermissionHelper::packagePermission($user->id);
            if (!empty($user)) {
                $permissions = json_decode($permissions, true);
                if (!in_array($permission, $permissions)) {
                    session()->flash('warning', 'Your package does not have permission to access this resource');
                    return redirect()->route('user-dashboard');
                }
            }
        }
        return $next($request);
    }
}
