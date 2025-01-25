<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VolunteerRequest extends FormRequest
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
            'id'=>$this->id,
            'name'=>$this->user->name,
            'email'=>$this->user->email,
            'role'=>$this->user->role,
            'logo'=>$this->user->logo,
            'desc'=>$this->desc,
            'address'=>$this->address,
            'phone'=>$this->phone,
            'isApproved'=>$this->isApproved,
        ];
    }
}
