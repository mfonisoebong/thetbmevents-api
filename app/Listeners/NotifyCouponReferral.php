<?php

namespace App\Listeners;

use App\Events\TicketPurchaseCompleted;
use App\Mail\CouponReferralMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class NotifyCouponReferral
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
    public function handle(TicketPurchaseCompleted $event): void
    {
        $referralEmail = $event->transaction->coupon?->referral_email;

        if (!$referralEmail) return;

        Mail::to($referralEmail)->send(new CouponReferralMail($event->transaction));
    }
}
