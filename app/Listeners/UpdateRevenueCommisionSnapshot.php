<?php

namespace App\Listeners;

use App\Events\RevenueOverview;
use App\Models\RevenueCommisionSnapshot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateRevenueCommisionSnapshot
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
    public function handle(RevenueOverview $event): void
    {
        $snapshot= RevenueCommisionSnapshot::first();
        
        $sameRevenue= $snapshot?->net_revenue === $event->revenueCommision['net_revenue'];
        $sameCommision= $snapshot?->net_commision ===$event->revenueCommision['net_commision'];

        if($sameCommision && $sameRevenue) return;

        RevenueCommisionSnapshot::truncate();

        RevenueCommisionSnapshot::create($event->revenueCommision);
    }
}
