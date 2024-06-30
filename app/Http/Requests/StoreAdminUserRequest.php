<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminUserRequest extends FormRequest
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
            'first_name'=> ['required', 'string'],
            'last_name'=> ['required', 'string'],
            'admin_role'=> ['required', Rule::in(['super_admin', 'support', 'manager'])],
            'phone_number'=> ['required'],
            'phone_dial_code'=> ['required'],
            'email'=> ['required', 'email', Rule::unique('users', 'email')],
            'country'=> ['required']
        ];
    }
}
