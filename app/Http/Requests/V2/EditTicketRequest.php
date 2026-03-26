<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class EditTicketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'end_selling_date' => 'required|date|after_or_equal:now',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
