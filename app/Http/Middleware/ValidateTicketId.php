<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTicketId
{
    use HttpResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ticketId= $request->ticket_id;

        $ticket= Ticket::where('id', '=', $ticketId)->first();

        if(!$ticket){
            return $this->failed(404, null, 'Ticket was not found');
        }

        return $next($request);
    }
}
