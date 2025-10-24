<?php

namespace App\Http\Resources\Mobile\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
            'country' => $this->country,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'account_state' => $this->account_state,
            'phone_dial_code' => $this->phone_dial_code,
            'avatar' => $this->avatar,
            'completed_profile' => $this->completed_profile,
            'auth_provider' => $this->auth_provider,
            'buisness_name' => $this->buisness_name,
            'email_verified_at' => $this->email_verified_at,
            'admin_role' => $this->admin_role,
        ];
    }
}
