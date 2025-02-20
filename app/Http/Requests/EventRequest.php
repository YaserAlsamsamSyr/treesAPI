<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:400'],
            'desc' => ['required', 'string'],
            'orgName' => ['required', 'string', 'max:255'],
            'orgOwnerName' => ['required', 'string', 'max:255'],
            'startDate' => ['required', 'string', 'max:255'],
            'endDate' => ['required', 'string', 'max:255'],
            'images' => ['nullable','array'],
            'images.*' => ['nullable','image','mimes:jpeg,jpg,png'],
        ];
    }
}
