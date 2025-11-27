<?php

namespace App\Http\Resources\Mobile\Event;

use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EventCategory */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'category' => $this->category,
            'slug' => $this->slug,
            'icon' => $this->icon ? asset($this->icon) : null,
        ];
    }
}
