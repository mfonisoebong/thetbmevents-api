<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLinkVerification extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'hash',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'timestamp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
