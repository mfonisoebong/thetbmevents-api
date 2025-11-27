<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        $category = $this->route('category');
        return [
            'category' => ['required', 'string', 'unique:event_categories,category,' . $category->id . ',id'],
            'slug' => ['required', 'string', 'unique:event_categories,slug,' . $category->id . ',id'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
