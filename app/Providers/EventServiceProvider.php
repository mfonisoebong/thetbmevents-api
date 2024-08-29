<?php

namespace App\Providers;

use App\Events\InvoiceGenerated;
use App\Events\PasswordTokenCreated;
use App\Events\RevenueOverview;
use App\Events\UserRegistered;
use App\Listeners\NotifyAdmin;
use App\Listeners\NotifyAdminAndOrganizersOnPayment;
use App\Listeners\NotifyCouponReferral;
use App\Listeners\SendInvoice;
use App\Listeners\SendOTPCode;
use App\Listeners\SendPasswordResetEmail;
use App\Listeners\SendPurchasedTickets;
use App\Listeners\SendWelcomeMail;
use App\Listeners\UpdateOrganizerStats;
use App\Listeners\UpdateRevenueCommisionSnapshot;
use App\Listeners\UpdateTicketStats;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserRegistered::class => [
            SendWelcomeMail::class,
            NotifyAdmin::class,
        ],
        InvoiceGenerated::class => [
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
