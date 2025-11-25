<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreEventRequest extends FormRequest
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
            'title' => ['required'],
            'type' => ['required'],
            'description' => ['required'],
            'event_date' => ['required'],
            'timezone' => ['required'],
            'longitude' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
            'undisclose_location' => ['required', 'in:true,false'],
            'logo' => ['required', File::image()],
            'categories' => ['required'],
            'tickets.*' => ['required'],
            'tickets' => ['array', 'required'],

        ];
    }
}

