<?php

namespace App\Arrays;

class SupportedGateways
{
    public static function get(): array
    {
        return ['paystack', 'flutterwave', 'chainpal'];
    }
}
