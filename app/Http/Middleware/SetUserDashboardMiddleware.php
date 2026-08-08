<?php

namespace App\Http\Middleware;

use App\Models\Language as AdminLanguage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserDashboardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (session()->has('user_lang')) {
            app()->setLocale('user_' .session()->get('user_lang'));
        } else {
            $default = AdminLanguage::where('is_default', 1)->first();
            if (!empty($default)) {
                app()->setLocale('user_' . $default->code);
            }
        }
        return $next($request);

    }
}
