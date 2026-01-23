<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\NotifyAdminOnNewSignup;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOnNewSignup
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        Mail::to(config('mail.admin_email'))->send(new NotifyAdminOnNewSignup($event->user));
    }
}
