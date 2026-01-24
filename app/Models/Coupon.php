<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'start_date_time',
        'end_date_time',
        'type',
        'value',
        'event_id',
        'user_id',
        'status',
        'referral_name',
        'referral_email',
        'limit',
    ];

    protected $casts = [
        'value' => 'float',
        'limit' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsExpiredAttribute(): bool
    {
        $endDate = Carbon::parse($this->end_date_time);
        return Carbon::now()->greaterThan($endDate);
    }

    public function getHasReachedLimitAttribute(): bool
    {
        return $this->limit === 0;
    }

    public function getIsActiveAttribute(): bool
    {
        $startDate = Carbon::parse($this->start_date_time);
        $endDate = Carbon::parse($this->end_date_time);
        return Carbon::now()->between($startDate, $endDate);
    }

    public function calculateValue($amount): float
    {
        if ($this->type === 'percentage') {
            return $amount * ($this->value / 100);
        }

        return $this->value;
    }

    public function invoices()
    {
        return $this->hasMany(Transaction::class);
    }
}
