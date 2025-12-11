<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable= [
        'gateway',
        'flutterwave_live_key',
        'flutterwave_test_key',
        'paystack_test_key',
        'paystack_live_key'
    ];


}
