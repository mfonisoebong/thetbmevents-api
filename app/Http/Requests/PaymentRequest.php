<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            'customer_first_name' => ['required'],
            'customer_last_name' => ['required'],
            'customer_email' => ['required', 'email'],
            'customer_phone_dial_code' => ['required'],
            'customer_phone_number' => ['required', 'numeric'],
            'attendees.*.first_name' => ['required'],
            'attendees.*.last_name' => ['required'],
            'attendees.*.email' => ['required', 'email'],
            'attendees.*.ticket_id' => ['required'],
            'tickets.*.id' => ['required'],
            'tickets.*.quantity' => ['required'],
        ];
    }
}
