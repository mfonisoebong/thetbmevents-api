<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Ticket;
use App\Policies\CouponPolicy;
use App\Policies\EventPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Coupon::class => CouponPolicy::class,
        Ticket::class => TicketPolicy::class,
        Event::class => EventPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
