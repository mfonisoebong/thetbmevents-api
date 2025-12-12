<?php

namespace App\Listeners;

use App\Events\Mobile\UserRegisteredEvent as MobileUserRegistered;
use App\Events\UserRegistered;
use App\Mail\NotifyAdminForNewUser;
use Illuminate\Support\Facades\Mail;

class NotifyAdmin
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
    public function handle(UserRegistered|MobileUserRegistered $event): void
    {
        Mail::to(env('ADMIN_MAIL_ADDRESS'))
            ->send(new NotifyAdminForNewUser($event->user));
    }
}
