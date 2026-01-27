<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'event_date',
        'event_time',
        'description',
        'category',
        'tags',
        'location',
        'image_url',
        'type',
        'event_link',
        'links_instagram',
        'links_twitter',
        'links_facebook',
        'timezone',
        'undisclose_location',
        'alias',
        'location_tips',
        'is_featured',
        'longitude',
        'latitude',
        'status'
    ];

    protected $casts = [
        'tags' => 'array',
        'undisclose_location' => 'boolean',
    ];

    public function scopeFilter(Builder $builder)
    {

        $builder->when(request('search'), function ($builder) {
            $builder->where('title', 'like', '%' . request('search') . '%')
                ->orWhere('category', 'like', '%' . request('search') . '%')
                ->orWhere('location', 'like', '%' . request('search') . '%');
        });

        $builder->when(request('status'), function ($builder) {
            $builder->where('status', request('status'));
        });

        $builder->when(request('category'), function ($builder) {
            $builder->where('category', 'like', '%' . request('category') . '%');
        });

        $builder->when(request('location'), function ($builder) {
            $builder->where('location', 'like', '%' . request('location') . '%');
        });
        $builder->when(request('date'), function ($builder) {

            if (request('date') === 'today') {
                $builder->where('event_date', '=', now()->format('Y-m-d'));
                return;
            }

            if (request('date') === 'this_week') {
                $today = now();
                $builder->whereBetween('event_date', [
                    $today->startOf('week'),
                    $today->endOf('week')
                ]);
                return;
            }

            if (request('date') === 'this_month') {
                $today = now();
                $builder->whereBetween('event_date', [
                    $today->startOf('month'),
                    $today->endOf('month'),
                ]);
                return;
            }

            $builder->where('event_date', '=', request('date'));
        });


    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class, 'event_id');
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($event) {
            $event->alias = Str::slug($event->title) . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        });
    }

    public function getStartDateTimeAttribute()
    {
        return $this->commence_date . ' ' . $this->commence_time;
    }

    public function getEndDateTimeAttribute()
    {
        return $this->end_date . ' ' . $this->end_time;
    }

    public function getImageUrlAttribute($value)
    {
        if(Str::isUrl($value)) return $value;

        return config('app.url') . '/' . trim($value, '/');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

}
