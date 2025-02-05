<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\AdvertisementsResource;

class PlantsStoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->persone->id,
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
            'rejectDesc'=>$this->rejectDesc,
            'adminApproved'=>$this->adminApproved,
            'rate'=>$this->rate,
            'images'=>ImageResource::collection($this->images),
            'waiting_trees'=>AdvertisementsResource::collection($this->advertisements()->where('status','wait')->paginate(10)),
            'done_trees'=>AdvertisementsResource::collection($this->advertisements()->where('status','done')->paginate(10)),
            'false_trees'=>AdvertisementsResource::collection($this->advertisements()->where('status','false')->paginate(10))
        ];
    }
}
