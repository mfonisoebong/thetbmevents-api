<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordByOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'otp' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

