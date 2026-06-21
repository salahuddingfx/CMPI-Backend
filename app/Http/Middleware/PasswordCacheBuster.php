<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PasswordCacheBuster
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('api/v1/logout')) {
            Cache::forget("user_pass_" . $request->user()->id);
        }

        return $response;
    }
}
