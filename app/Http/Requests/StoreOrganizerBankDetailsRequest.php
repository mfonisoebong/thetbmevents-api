<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizerBankDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        
        $isPermitted= request()->user()?->bankDetails ? false: true;

        return $isPermitted;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'bank_name'=> ['required', 'string'],
            'account_number'=> ['required', 'string'],
            'account_name'=> ['required', 'string'],
            'swift_code'=> ['string', 'nullable'],
            'iban'=> ['string', 'nullable'],
        ];
    }
}
