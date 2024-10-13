<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasedTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'attendee_id',
        'quantity',
        'price',
        'used',
        'invoice_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }
}
