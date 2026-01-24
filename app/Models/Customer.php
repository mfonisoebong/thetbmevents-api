<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        "full_name",
        "email",
        "phone_number",
        'tickets_bought_count'
    ];

    public function invoice()
    {
        return $this->hasOne(Transaction::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
