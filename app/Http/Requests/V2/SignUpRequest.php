<?php

namespace App\Http\Requests\V2;

use App\Arrays\UserRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SignUpRequest extends FormRequest
{
    public function prepareForValidation(): void
    {
        $this->merge([
            'role' => $this->input('role', 'organizer'),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => 'nullable|string|max:255',
            'role' => ['required', Rule::in(UserRoles::get())],
            'business_name' => ['required_if:role,organizer', 'string', 'max:255'],
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'country' => 'required|string|max:2',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
