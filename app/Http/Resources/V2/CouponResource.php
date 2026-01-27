<?php

namespace App\Http\Resources\V2;

use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'event_name' => Event::find($this->event_id)->title,
            'start_date_time' => $this->start_date_time,
            'end_date_time' => $this->end_date_time,
            'limit' => $this->limit,
            'used_count' => Transaction::where('coupon_id', $this->id)->count(),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
