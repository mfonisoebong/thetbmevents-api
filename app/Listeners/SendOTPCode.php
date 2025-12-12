<?php

namespace App\Listeners;

use App\Events\Mobile\UserRegisteredEvent as MobileUserRegistered;
use App\Events\UserRegistered;
use App\Mail\OtpCode;
use App\Models\OtpVerification;
use Illuminate\Support\Facades\Mail;

class SendOTPCode
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
        $otpCode = random_int(100000, 999999);

        $otp = OtpVerification::create([
            'user_id' => $event->user->id,
            'otp' => $otpCode,
            'type' => 'email_verification',
        ]);

        Mail::to($event->user)
            ->send(new OtpCode($event->user, $otp));
    }
}
