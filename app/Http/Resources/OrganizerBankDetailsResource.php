<?php

namespace App\Http\Resources;

use App\Models\OrganizerBankDetails;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrganizerBankDetails */
class OrganizerBankDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'swift_code' => $this->swift_code,
            'iban' => $this->iban,
        ];
    }
}
