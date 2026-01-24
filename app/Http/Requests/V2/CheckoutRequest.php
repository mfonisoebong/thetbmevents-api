<?php

namespace App\Http\Requests\V2;

use App\Arrays\SupportedGateways;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tickets' => 'required|array|min:1',
            'tickets.*' => 'required|string|exists:tickets,id',
            'customer' => 'required|array',
            'customer.fullname' => 'required|string|max:255',
            'customer.email' => 'required|string|email|max:255',
            'customer.phone' => 'required|string|max:20',
            'send_to_different_email' => 'required|boolean',
            'attendees' => 'required_if:send_to_different_email,true|array',
            'attendees.*.fullname' => 'required|string|max:255',
            'attendees.*.email' => 'required|string|email|max:255',
            'is_free_checkout' => 'required|boolean',
            'gateway' => ['required_if:is_free_checkout,false', Rule::in(SupportedGateways::get())],
            'coupon_applied' => 'required|boolean',
            'coupon_code' => 'required_if:coupon_applied,true|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
