<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;

class EventResource extends JsonResource
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
            'title'=>$this->title,
            'address'=>$this->address,
            'desc'=>$this->desc,
            'startDate'=>$this->startDate,
            'endDate'=>$this->endDate,
            'orgName'=>$this->orgName,
            'images'=>ImageResource::collection($this->images)
        ];
    }
}
