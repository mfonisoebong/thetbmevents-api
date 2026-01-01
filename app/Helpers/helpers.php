<?php

function currencyFormatter($amount, ?string $currency = '₦'): string
{
    return $currency . ' ' . number_format($amount, 2);
}
