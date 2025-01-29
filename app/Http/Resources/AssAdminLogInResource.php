<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\PlantsStoreResource;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\AdvertisementsResource;
use App\Http\Resources\WorkResource;
use App\Http\Resources\CategoryResource;
use App\Models\Volunteer;
use App\Models\Planstore;
use App\Models\Advertisement;
use App\Models\Work;
use App\Models\Category;

class AssAdminLogInResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'profile'=>[
                'id'=>$this->admin->id,
                'name'=>$this->name,
                'email'=>$this->email,
                'logo'=>$this->logo,
                'role'=>$this->role,
                'orgName'=>$this->admin->orgName,
                'desc'=>$this->admin->desc,
                'address'=>$this->admin->address,
                'phone'=>$this->admin->phone,
                'images'=>ImageResource::collection($this->admin->images)
            ],
            'volunteerQue'=>VolunteerResource::collection(Volunteer::where('isApproved','pin')->paginate(10)),
            'allVolunterr'=>VolunteerResource::collection(Volunteer::where('isApproved','!=','pin')->paginate(10)),
            'allPlantsStore'=>PlantsStoreResource::collection(Planstore::where('isApproved','!=','pin')->paginate(10)),
            'plansStoreQue'=>PlantsStoreResource::collection(Planstore::where('isApproved','pin')->paginate(10)),
            'treeQue'=>AdvertisementsResource::collection(Advertisement::where('volunteer_id','!=',null)->paginate(10)),
            'WorkQue'=>WorkResource::collection(Work::where('volunteer_id','!=',null)->paginate(10)),
            'categories'=>CategoryResource::collection(Category::get())

        ];
    }
}
