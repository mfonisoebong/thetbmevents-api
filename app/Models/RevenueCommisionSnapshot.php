<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueCommisionSnapshot extends Model
{
    use HasFactory;

    protected $fillable= [
        'net_revenue',
        'net_commision'
    ];


}
