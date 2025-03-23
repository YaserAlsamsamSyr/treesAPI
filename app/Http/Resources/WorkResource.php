<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;

class WorkResource extends JsonResource
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
            'desc'=>$this->desc,
            'address'=>$this->address,
            'images'=>ImageResource::collection($this->images),
            'status'=>$this->status,
            'isDone'=>$this->isDone,
            'mac'=>$this->mac,
            'volunterrName'=>$this->volunteer->user->name ?? ''
            
        ];
    }
}
