<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizerBankDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'bank_name'=> ['string', ],
            'account_number'=> ['string', ],
            'account_name'=> ['string', ],
            'swift_code'=> ['string', 'nullable'],
            'iban'=> ['string','nullable' ],
        ];
    }
}
