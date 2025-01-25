<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ImageRequest;
use App\Http\Requests\AdvertisementsRequest;

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
            'id'=>$this->id,
            'name'=>$this->persone->name,
            'email'=>$this->persone->email,
            'role'=>$this->persone->role,
            'logo'=>$this->persone->logo,
            'address'=>$this->address,
            'phone'=>$this->phone,
            'ownerName'=>$this->ownerName,
            'desc'=>$this->desc,
            'openTime'=>$this->openTime,
            'closeTime'=>$this->closeTime,
            'isApproved'=>$this->isApproved,
            'images'=>ImageRequest::collection($this->images),
            'waiting_trees'=>AdvertisementsRequest::collection($this->advertisements()->where('status','wait')->paginate(10)),
            'done_trees'=>AdvertisementsRequest::collection($this->advertisements()->where('status','done')->paginate(10)),
            'false_trees'=>AdvertisementsRequest::collection($this->advertisements()->where('status','false')->paginate(10))
        ];
    }
}
