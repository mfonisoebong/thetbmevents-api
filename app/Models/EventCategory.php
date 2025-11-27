<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'slug',
        'icon'
    ];

    public function scopeFilter(Builder $builder)
    {

        $builder->when(request('search'), function ($builder) {
            $searchValue = '%' . request('search') . '%';
            $builder->where('category', 'like', $searchValue)
                ->orWhere('slug', 'like', $searchValue);
        });
    }

    public function getIconUrlAttribute()
    {
        return $this->icon ? asset($this->icon) : null;
    }
}
