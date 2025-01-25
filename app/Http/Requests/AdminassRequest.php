<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ImageRequest;

class AdminassRequest extends FormRequest
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
            'id'=>$this->admin->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'logo'=>$this->logo,
            'role'=>$this->role,
            'orgName'=>$this->admin->orgName,
            'desc'=>$this->admin->desc,
            'address'=>$this->admin->address,
            'phone'=>$this->admin->phone,
            'images'=>ImageRequest::collection($this->admin->images)
        ];
    }
}
