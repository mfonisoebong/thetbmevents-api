<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    use HasFactory;

    protected $fillable = [
        "first_name",
        "last_name",
        "email",
        "ticket_id",
        "customer_id"
    ];

    public function getFullNameAttribute()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        return $name;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function purchasedTicket()
    {
        return $this->hasOne(PurchasedTicket::class);
    }
}
