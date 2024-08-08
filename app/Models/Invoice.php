<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'payment_method',
        'cart_items',
        'transaction_reference',
        'payment_status',
        'coupon_id',
        'coupon_amount',
        'amount',
        'organizer_id'
    ];

    public function scopeFilter(Builder $builder)
    {
        $builder->when(request('from'), function ($builder) {
            $startDate = Carbon::parse(request('from'));
            $endDate = Carbon::parse(request('to'));
            $builder->whereBetween('created_at', [$startDate, $endDate]);
        });

        $builder->when(request('year'), function ($builder) {
            $builder
                ->whereYear('created_at', request('year'))
                ->whereMonth('created_at', request('month'));
        });


        $builder->when(request('today'), function ($builder) {
            $today = Carbon::now()
                ->toDateString();
            $builder->whereDate('created_at', $today);
        });

        $builder->when(request('yesterday'), function ($builder) {
            $yesterday = Carbon::yesterday()
                ->toDateString();
            $builder->whereDate('created_at', $yesterday);
        });

        $builder->when(request('past_three_months'), function ($builder) {
            $threeMonthsAgo = Carbon::now()->subMonths(3);
            $builder->where('created_at', '>=', $threeMonthsAgo);
        });

        return $builder;
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
