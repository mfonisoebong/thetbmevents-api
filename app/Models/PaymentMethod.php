<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable= [
        'gateway',
        'vella_tag',
        'vella_webhook_url',
        'vella_live_key',
        'paystack_webhook_url',
        'vella_test_key',
        'paystack_test_key',
        'paystack_live_key'
    ];

    
}
