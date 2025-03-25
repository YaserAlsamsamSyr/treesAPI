<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;

class AdvertisementsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $volun='';
        return $this->volunteer_id;
            $volun=$this->volunteer->user->name;
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'desc'=>$this->desc,
            'status'=>$this->status,
            'isDone'=>$this->isDone,
            'images'=>ImageResource::collection($this->images),
            'plantsStoreName'=>$this->planstore->persone->name,
            'volunterrName'=>$volun
        ];
    }
}
