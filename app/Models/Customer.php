<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        "first_name",
        "last_name",
        "email",
        "phone_dial_code",
        "phone_number"
    ];

    public function getFullNameAttribute()
    {
        $name = $this->first_name . ' ' . $this->last_name;
        return $name;
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}
