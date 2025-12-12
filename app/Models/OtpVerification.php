<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'otp',
        'type',
        'expires_at'
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->expires_at = now()->addMinutes(30);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
