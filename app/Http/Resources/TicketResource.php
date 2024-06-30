<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        $ticketSellingEndDate= Carbon::parse($this->selling_end_date_time);
        $ticketSellingStartDate= Carbon::parse($this->selling_start_date_time);
        $now= Carbon::now();
        $isEarly= $now->lt($ticketSellingStartDate);
        $isLate= $now->gt($ticketSellingEndDate);

        return [
            'id'=> $this->id,
            'name'=> $this->name,
            'price'=> (float)$this->price,
            'quantity'=> (int)$this->quantity,
            'unlimited'=> (bool)$this->unlimited,
            'organizer_id'=> $this->organizer_id,
            'description'=> Str::limit($this->description, 150),
            'selling_start_date_time'=> $this->selling_start_date_time,
            'selling_end_date_time'=> $this->selling_end_date_time,
            'is_early'=> $isEarly,
            'is_late'=> $isLate,
            'sold'=> $this->sold,
            'created_at'=> $this->created_at
        ];
    }
}
