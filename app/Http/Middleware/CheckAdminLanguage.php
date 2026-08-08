<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Language;


class CheckAdminLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (session()->has('admin_lang')) {
            app()->setLocale(session()->get('admin_lang'));
        } else {
            $default = Language::where('is_default', 1)->first();
            if (!empty($default)) {
                app()->setLocale('admin_' . $default->code);
            }
        }

        return $next($request);
    }
}
