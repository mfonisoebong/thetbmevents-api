<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestimoniesRequest extends FormRequest
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
            'heading'=> ['required', 'string'],
            'sub_heading'=> ['required', 'string'],
            'names'=> ['required', 'array'],
            'descriptions'=> ['required', 'array'],
            'channels'=> ['required', 'array'],
            'avatars'=> ['array', 'required']
        ];
    }
}
