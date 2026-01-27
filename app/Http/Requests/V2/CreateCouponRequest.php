<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class CreateCouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|string|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'limit' => 'required|integer|min:-1',
            'event_id' => 'required|exists:events,id',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after:start_date_time',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
