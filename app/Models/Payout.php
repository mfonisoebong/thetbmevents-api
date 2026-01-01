<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{

    protected $fillable = [
        'user_id',
        'amount',
        'organizer_bank_details_id',
        'status',
    ];

    public function scopeFilter(Builder $builder)
    {
        $builder->when(request('status'), function ($builder) {
            $builder->where('status', request('status'));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organizerBankDetails(): BelongsTo
    {
        return $this->belongsTo(OrganizerBankDetails::class);
    }
}
