<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;

class AdminassResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'logo'=>$this->logo,
            'role'=>$this->role,
            'orgName'=>$this->admin->orgName,
            'desc'=>$this->admin->desc,
            'address'=>$this->admin->address,
            'phone'=>$this->admin->phone,
            'images'=>ImageResource::collection($this->admin->images)
        ];
    }
}
