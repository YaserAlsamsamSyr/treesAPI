<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'desc' => ['required', 'string', 'max:700'],
            'address' => ['required', 'string', 'max:500'],
            'mac'=>['required','string','max:30'],
            'images' => ['nullable','array'],
            'images.*' => ['nullable','image','mimes:jpeg,jpg,png'],
        ];
    }
}
