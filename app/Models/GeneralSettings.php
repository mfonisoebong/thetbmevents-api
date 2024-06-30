<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSettings extends Model
{
    use HasFactory;

    protected $fillable= [
        'site_name',
        'site_description',
        'site_logo_light',
        'site_logo_dark'
    ];
}
