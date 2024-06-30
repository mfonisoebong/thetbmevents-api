<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizerActivated
{
    use HttpResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user= $request?->user();
        $accountStatus= $user->account_state;
        $deactivatedUser= $accountStatus!=='active';

        if($deactivatedUser){
            return $this->failed(403);
        }

        return $next($request);
    }
}
