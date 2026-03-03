<?php

namespace App\Arrays;

class EventEagerLoads
{
    public static function get(): array
    {
        return ['user', 'tickets', 'tickets.purchasedTickets', 'tickets.newPurchasedTickets'];
    }
}
