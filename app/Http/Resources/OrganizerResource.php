<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'name'=> $this->full_name,
            'id'=> $this->id,
            'email'=> $this->email,
            'avatar'=>  $this->avatar,
            'phone'=> '('.$this->phone_dial_code.') - '.$this->phone_number,
            'created_at'=> $this->created_at,
            'role'=> $this->role,
            'account_status'=> $this->account_state,
            'bank_details'=> $this->bankDetails,
            'commision'=> $this->commision
        ];
    }
}
