<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;

class ThrottleWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'whatsapp_webhook:' . $request->ip();
        $limiter = app(RateLimiter::class);

        if ($limiter->tooManyAttempts($key, 60)) {
            return response('Too Many Attempts', 429);
        }

        $limiter->hit($key, 60);

        return $next($request);
    }
}
