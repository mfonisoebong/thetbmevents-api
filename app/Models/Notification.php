<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable= [
      'body',
      'user_id',
        'unread'
    ];

    protected $hidden= [
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(Notification::class);
    }

}
