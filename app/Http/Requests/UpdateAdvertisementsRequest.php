<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'plantsStoreName' => ['nullable', 'string', 'max:255'],
            'desc' => ['nullable', 'string'],
            'images' => ['nullable','array'],
            'images.*' => ['nullable','image','mimes:jpeg,jpg,png'],
        ];
    }
}
