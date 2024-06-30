<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $adminRole= $guards[1] ?? null;


        $user= $request->user();

        if($user?->role !== $role){
            return  $this->failed(403, null, 'You are not authorized to access this route');
        }

        if($user?->admin_role === 'super_admin'){
            return $next($request);
        }

        if($adminRole && $user?->admin_role!== $adminRole){
            return  $this->failed(403, null, 'You are not authorized to access this route');
        }

        return $next($request);
    }
}
