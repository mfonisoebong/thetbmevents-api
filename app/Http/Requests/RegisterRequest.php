<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        // make role optional with default value 'organizer'
        if (!$this->has('role')) {
            $this->merge([
                'role' => 'organizer',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required_if:role,customer,admin', 'string', 'max:255'],
            'last_name' => ['required_if:role,customer,admin', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'organizer', 'customer'])],
            'buisness_name' => ['required_if:role,organizer', 'string', 'max:255'],
            'phone_number' => ['required'],
            'phone_dial_code' => ['required'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:6', 'max:30', 'confirmed'],
            'country' => ['required']
        ];
    }
}
