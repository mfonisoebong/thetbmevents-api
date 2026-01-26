<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\Ticket;

class OrganizerTicketController extends Controller
{
    public function index()
    {

    }

    public function deleteTicket(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully.'
        ], 200);

    }
}
