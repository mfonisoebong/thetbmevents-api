<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable= [
      'ticket_id',
      'owner',
      'quantity',
      'status',
      'tid'
    ];
}
