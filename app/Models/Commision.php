<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commision extends Model
{
    use HasFactory, HasUuids;

    protected $fillable= [
      'rate',
      'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
