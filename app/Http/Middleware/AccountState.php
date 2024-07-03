<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountState
{
    use HttpResponses;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $state = $guards[0];
        $user = $request?->user();

        if ($user?->account_state !== $state) {
            $this->failed(403, null, 'Account is not active');
        }

        return $next($request);
    }
}
