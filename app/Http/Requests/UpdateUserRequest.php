<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'country'=> ['required'],
            'email'=> ['required', 'email'],
            'phone_number'=> ['required'],
            'phone_dial_code'=> ['required'],
            'first_name'=> ['nullable'],
            'last_name'=> ['nullable'],
            'business_name'=>['nullable'],
        ];
    }
}
