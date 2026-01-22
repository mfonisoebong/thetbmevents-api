<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'=> $this->id,
            'full_name'=> $this->full_name,
            'business_name'=> $this->business_name,
            'completed_profile'=> $this->completed_profile,
            'avatar'=> $this->avatar,
            'auth_provider'=> $this->auth_provider,
            'email'=> $this->email,
            'role'=> $this->role,
            'country'=> $this->country,
            'phone_number'=> $this->phone_number,
            'account_state'=> $this->account_state,
            'created_at'=> $this->created_at,
            // todo: remove temporary fields in the future
            'account_status'=> $this->account_state,
            'admin_role' => $this->role === 'admin' ? 'super_admin' : null,
            'first_name' => explode(' ', $this->full_name)[0] ?? null,
            'last_name' => explode(' ', $this->full_name)[1] ?? null
        ];
    }
}
