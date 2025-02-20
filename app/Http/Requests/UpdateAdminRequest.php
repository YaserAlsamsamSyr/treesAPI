<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateAdminRequest extends FormRequest
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
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'logo'=>['nullable','image','mimes:jpeg,png,jpg'],
            'orgName' => ['nullable', 'string', 'max:255'],
            'desc' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{7,15}$/'],
            'imgs' => ['nullable','array'],
            'imgs.*' => ['nullable','image','mimes:jpeg,jpg,png'],
        ];
    }
}
