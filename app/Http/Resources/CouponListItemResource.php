<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponListItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $invoicesCount = $this->invoices()->count();

        return [
            'id' => (string)$this->id,
            'code' => $this->code,
            'status' => $this->status,
            'start_date_time' => Carbon::parse($this->start_date_time)->format('Y-m-d H:i:s'),
            'end_date_time' => Carbon::parse($this->end_date_time)->format('Y-m-d H:i:s'),
            'type' => $this->type,
            'value' => $this->value,
            'event' => $this->event->title,
            'event_id' => $this->event_id,
            'limit' => $this->limit,
            'referral_name' => $this->referral_name,
            'is_expired' => $this->is_expired,
            'invoices_count' => $invoicesCount,
        ];
    }
}
