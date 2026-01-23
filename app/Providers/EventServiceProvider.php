<?php

namespace App\Providers;

use App\Events\PasswordTokenCreated;
use App\Events\RevenueOverview;
use App\Events\TicketPurchaseCompleted;
use App\Events\UserRegistered;
use App\Listeners\NotifyAdminAndOrganizersOnPayment;
use App\Listeners\NotifyAdminOnNewSignup;
use App\Listeners\NotifyCouponReferral;
use App\Listeners\SendOTPCode;
use App\Listeners\SendPasswordResetEmail;
use App\Listeners\SendPurchasedTickets;
use App\Listeners\SendWelcomeMail;
use App\Listeners\UpdateOrganizerStats;
use App\Listeners\UpdateRevenueCommisionSnapshot;
use App\Listeners\UpdateTicketStats;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserRegistered::class => [
            SendOTPCode::class,
            SendWelcomeMail::class,
            NotifyAdminOnNewSignup::class,
        ],
        TicketPurchaseCompleted::class => [
            UpdateTicketStats::class,
            NotifyAdminAndOrganizersOnPayment::class,
            UpdateOrganizerStats::class,
            SendPurchasedTickets::class,
            NotifyCouponReferral::class
        ],
        PasswordTokenCreated::class => [
            SendPasswordResetEmail::class
        ],
        RevenueOverview::class => [
            UpdateRevenueCommisionSnapshot::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
