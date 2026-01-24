<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'event_id',
        'price',
        'name',
        'unlimited',
        'quantity',
        'organizer_id',
        'description',
        'selling_start_date_time',
        'selling_end_date_time',
        'sold'
    ];

    protected $casts = [
        'selling_start_date_time' => 'datetime',
        'selling_end_date_time' => 'datetime',
        'price' => 'float',
        'quantity' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchasedTickets()
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
