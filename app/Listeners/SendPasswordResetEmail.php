<?php

namespace App\Listeners;

use App\Events\PasswordTokenCreated;
use App\Mail\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmail
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
    public function handle(PasswordTokenCreated $event): void
    {
        Mail::to($event->token->user->email)
        ->send(new PasswordReset($event->token));
        
    }
}
