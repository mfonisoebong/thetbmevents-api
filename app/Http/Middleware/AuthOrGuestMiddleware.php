<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthOrGuestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            $request->setUserResolver(function () {
                return null;
            });
            return $next($request);
        }

        // Fetch the user and set the authenticated user for this request
        $accessToken = PersonalAccessToken::findToken($token);
        if ($accessToken) {
            $user = $accessToken->tokenable;
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        return $next($request);
    }
}
