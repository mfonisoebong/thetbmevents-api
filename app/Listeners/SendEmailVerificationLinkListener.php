<?php

namespace App\Listeners;

use App\Events\ResendEmailVerificationLinkEvent;
use App\Events\UserRegistered;
use App\Mail\OtpCode;
use App\Mail\VerifyEmailViaLink;
use App\Models\EmailLinkVerification;
use Illuminate\Support\Facades\Mail;

class SendEmailVerificationLinkListener
{
    public function __construct()
    {
    }

    public function handle(UserRegistered | ResendEmailVerificationLinkEvent $event): void
    {
        $hash = sha1($event->user->email . now());

        EmailLinkVerification::create([
            'user_id' => $event->user->id,
            'hash' => $hash,
            'expires_at' => now()->addMinutes(30),
        ]);

        Mail::to($event->user)->send(new VerifyEmailViaLink($event->user, $hash));
    }
}
