<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Sale extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organizer_id',
        'customer_id',
        'ticket_id',
        'tickets_bought',
        'total',
        'event_id',
        'invoice_id',
        'created_at'
    ];

    public function scopeFilter(Builder $builder)
    {
//        Filter with date range
        $builder->when(request('from'), function ($builder) {
            $startDate = Carbon::parse(request('from'));
            $endDate = Carbon::parse(request('to'));
            $builder->whereBetween('created_at', [$startDate, $endDate]);
        });

//        Filter with Month & Year
        $builder->when(request('year'), function ($builder) {
            $builder
                ->whereYear('created_at', request('year'))
                ->whereMonth('created_at', request('month'));
        });

//        Filter with today's date

        $builder->when(request('today'), function ($builder) {
            $today = Carbon::now()
                ->toDateString();
            $builder->whereDate('created_at', $today);
        });

//        Filter with yesterday's date
        $builder->when(request('yesterday'), function ($builder) {
            $yesterday = Carbon::yesterday()
                ->toDateString();
            $builder->whereDate('created_at', $yesterday);
        });

//            Filter with the past three months
        $builder->when(request('past_three_months'), function ($builder) {
            $threeMonthsAgo = Carbon::now()->subMonths(3);
            $builder->where('created_at', '>=', $threeMonthsAgo);
        });

        return $builder;

    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
