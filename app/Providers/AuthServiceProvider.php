<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\BankAccountDetails;
use App\Models\Payout;
use App\Policies\BankAccountDetailsPolicy;
use App\Policies\PayoutPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
        BankAccountDetails::class => BankAccountDetailsPolicy::class,
        Payout::class => PayoutPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
