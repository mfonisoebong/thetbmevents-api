<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
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
            'tickets.*.event_id'=> ['required'],
            'tickets.*.name'=> ['required'],
            'tickets.*.selling_start_date_time'=> ['required'],
            'tickets.*.selling_end_date_time'=> ['required'],
            'tickets.*.price'=> ['required', 'numeric'],
            'tickets.*.quantity'=> ['required', 'integer'],
            'tickets.*.description'=> ['required']
        ];
    }
}
