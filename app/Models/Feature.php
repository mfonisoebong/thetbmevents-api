<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable= [
        'title',
        'thumbnail'
    ];

    public function getThumbnailAttribute($value){
        $thumbnail= env('APP_URL').'/'.$value;
        return $thumbnail;
    }


}
