<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolunteerResource extends JsonResource
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
