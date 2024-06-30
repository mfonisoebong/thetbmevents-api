<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthIndividualOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userRole= $request
            ->user()
            ->role;
        $isIndividualOrAdmin= $userRole ==='individual' || $userRole==='admin' ;

        if(!$isIndividualOrAdmin){
            return $this->failed(403, null, 'Unauthorized');
        }

        return $next($request);
    }
}
