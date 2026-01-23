<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    use ApiResponses;
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = auth()->user();

        if (!$user || $user->account_state !== 'active') {
            return $this->error(null, 403, 'Unauthorized access: User is not active');
        }

        return $next($request);
    }
}
