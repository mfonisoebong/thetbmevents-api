<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// TODO: rewrite
class RoleAuthenticate
{
    use HttpResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $role= $guards[0];

        $user= $request->user();

        if($user?->role !== $role){
            return  $this->failed(403, null, 'You are not authorized to access this route');
        }

        return $next($request);
    }
}
