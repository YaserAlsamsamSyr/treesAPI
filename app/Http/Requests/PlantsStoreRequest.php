<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use App\Models\User;

class PlantsStoreRequest extends FormRequest
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
                'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'userName' => ['required', 'string', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'logo'=>['nullable','image','mimes:jpeg,png,jpg'],
                'desc' => ['required', 'string', 'max:700'],
                'address' => ['required', 'string', 'max:500'],
                'phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],
                'ownerName' => ['required', 'string', 'max:255'],
                'openTime' => ['required', 'string', 'max:100'],
                'closeTime' => ['required', 'string', 'max:100'],
                'imgs' => ['nullable','array'],
                'imgs.*' => ['nullable','image','mimes:jpeg,jpg,png'],
        ];
    }
}
