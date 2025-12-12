<?php

namespace App\Listeners;

use App\Events\Mobile\UserRegisteredEvent as MobileUserRegistered;
use App\Events\UserRegistered;
use App\Mail\WelcomeUser;
use Illuminate\Support\Facades\Mail;


class SendWelcomeMail
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
        Mail::to($event->user->email)
            ->send(new WelcomeUser($event->user));
    }
}
