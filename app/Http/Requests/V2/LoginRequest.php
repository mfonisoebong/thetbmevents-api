<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PasswordRules;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => 'required' // PasswordRules::rules(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
