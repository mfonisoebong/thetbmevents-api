<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\NotifyAdminForNewUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
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
    public function handle(UserRegistered $event): void
    {
        Mail::to(env('ADMIN_MAIL_ADDRESS'))
        ->send(new NotifyAdminForNewUser($event->user));
    }
}
