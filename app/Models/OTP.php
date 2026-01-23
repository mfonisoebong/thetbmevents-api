<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OTP extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'otps';

    protected $fillable = [
        'user_id',
        'otp',
        'expire_at',
    ];

    protected $casts = [
        'otp' => 'integer',
        'expire_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
