<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory, HasUuids;

    protected $fillable= ['email'];

    public function scopeFilter(Builder $builder){
        $builder->when(request('search'), function ($builder){
            $searchVal= '%'. request('search') .'%';
           $builder->where('email', 'like', $searchVal);
        });        
 
    }

}
