<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\OtpCode;
use App\Models\OtpVerification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
    public function handle(UserRegistered $event): void
    {
        $otpCode = random_int(100000, 999999);

        $otp = OtpVerification::create([
            'user_id' => $event->user->id,
            'otp' => $otpCode,
            'type' => 'email_verification',
        ]);

        Mail::to($event->user->email)
            ->send(new OtpCode($event->user, $otp));
    }
}
