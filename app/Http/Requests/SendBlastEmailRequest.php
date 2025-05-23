<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendBlastEmailRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'email_content' => ['required', 'string'],
            'event_ids' => ['required', 'array'],
            'event_ids.*' => ['required', 'string', 'exists:events,id'],
        ];
    }
}
