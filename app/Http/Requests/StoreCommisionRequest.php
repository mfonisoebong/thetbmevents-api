<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organizer= User::where('id', request('organizer_id'))
            ->first();
        $isPermitted= $organizer?->commision ? false: true;

        return $isPermitted;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'rate'=> ['required', 'numeric', 'min:0','max:100'],
            'organizer_id'=> ['required']
        ];
    }
}
