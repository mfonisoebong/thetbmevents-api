<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|exists:event_categories,category',
            'type' => 'required|string|in:physical,virtual',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'date' => 'required|date|after:now',
            'time' => 'required|date_format:H:i',
            'timezone' => 'required|string|timezone',
            'location' => 'required_if:type,physical|string|max:255',
            'virtual_link' => 'required_if:type,virtual|url|max:255',
            'tickets' => 'required|array|min:1',
            'tickets.*.name' => 'required|string|max:100',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:0',
            'tickets.*.start_selling_date' => 'required|date|before_or_equal:date',
            'tickets.*.end_selling_date' => 'required|date|after_or_equal::start_selling_date',
            'tickets.*.description' => 'nullable|string',
            'tickets.*.currency' => 'nullable|in:NGN,USD',
            'image_url' => 'nullable|string',
            'image' => 'required_if:image_url,null|image',
            'undisclosed' => 'required|boolean'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
