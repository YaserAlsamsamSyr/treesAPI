<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\PlantsStoreRequest;
use App\Http\Requests\VolunteerRequest;
use App\Http\Requests\ImageRequest;
use App\Http\Requests\AdvertisementsRequest;
use App\Http\Requests\WorkRequest;
use App\Http\Requests\CategoryRequest;
use App\Models\Volunteer;
use App\Models\Planstore;
use App\Models\Advertisement;
use App\Models\Work;
use App\Models\Category;

class AssAdminLogInRequest extends FormRequest
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
                'images'=>ImageRequest::collection($this->admin->images)
            ],
            'volunteerQue'=>VolunteerRequest::collection(Volunteer::where('isApproved','pin')->paginate(10)),
            'allVolunterr'=>VolunteerRequest::collection(Volunteer::where('isApproved','!=','pin')->paginate(10)),
            'allPlantsStore'=>PlantsStoreRequest::collection(Planstore::where('isApproved','!=','pin')->paginate(10)),
            'plansStoreQue'=>PlantsStoreRequest::collection(Planstore::where('isApproved','pin')->paginate(10)),
            'treeQue'=>AdvertisementsRequest::collection(Advertisement::where('volunteer_id','!=',null)->paginate(10)),
            'WorkQue'=>WorkRequest::collection(Work::where('volunteer_id','!=',null)->paginate(10)),
            'categories'=>CategoryRequest::collection(Category::get())

        ];
    }
}
