<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
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
            'event_id' => ['required', 'exists:events,id'],
            'code' => ['required', 'string'],
            'start_date_time' => ['required', 'date'],
            'end_date_time' => ['required', 'date', 'after:start_date_time'],
            'type' => ['required', 'in:percentage,fixed'],
            'value' => ['required', 'numeric'],
            'limit' => ['nullable', 'numeric', 'min:1'],
            'referral_name' => ['nullable', 'string'],
            'referral_email' => ['nullable', 'required_with:referral_name', 'email'],
        ];
    }
}
