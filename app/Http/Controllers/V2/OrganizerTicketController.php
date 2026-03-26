<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\EditTicketRequest;
use App\Models\Ticket;

class OrganizerTicketController extends Controller
{
    public function deleteTicket(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully.'
        ]);

    }

    public function editTicket(EditTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->name = $request->name;
        $ticket->description = $request->description;
        $ticket->price = $request->price;
        $ticket->quantity = $request->quantity;
        $ticket->selling_end_date_time = date('Y-m-d H:i:s', strtotime($request->end_selling_date));
        $ticket->save();

        return $this->success(null, "'$request->name' updated successfully.");
    }
}
