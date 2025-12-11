<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'otp',
        'type'
    ];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
