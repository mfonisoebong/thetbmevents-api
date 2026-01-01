<?php

namespace App\Http\Resources\Mobile\Wallet;

use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payout */
class PayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'created_at' => $this->created_at->format('d M, Y h:i A')
        ];
    }
}
