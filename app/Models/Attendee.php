<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendee extends Model
{
    protected $fillable = [
        "full_name",
        "email",
        "ticket_id",
        "customer_id",
        "tickets_bought_count"
    ];

    public function customer() : BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function ticket() : BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function purchasedTicket() : HasOne
    {
        return $this->hasOne(PurchasedTicket::class);
    }

    public function newPurchasedTickets(): HasMany
    {
        return $this->hasMany(NewPurchasedTicket::class);
    }
}
