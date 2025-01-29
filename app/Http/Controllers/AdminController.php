<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\WorkResource;
use App\Http\Resources\AdminResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminassResource;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\PlantsStoreResource;
use App\Http\Resources\AssAdminLogInResource;
use App\Http\Resources\AdvertisementsResource;
use App\Http\Resources\CategoryOnlyResource;
use App\Http\Resources\PostResource;
use Illuminate\Validation\Rules;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\Volunteer;
use App\Models\Planstore;
use App\Models\Admin;
use App\Models\Work;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UploadImageController;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\UpdateAdminRequest;

class AdminController extends Controller
{
    
    public function adminLogin(Request $req){
        try{
            $req->validate([
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'password' => ['required', Rules\Password::defaults()],
            ]);
            if(!auth()->attempt(['email' => $req->email, 'password' => $req->password,'role'=>'admin']))
                return response()->json(['message'=>'password or email not correct'],422);
           $token=auth()->user()->createToken('admin',expiresAt:now()->addDays(4),abilities:['admin'])->plainTextToken;
           $adminData=new AdminResource(auth()->user());
           return response()->json(['token'=>$token,'response'=>$adminData],200);
          } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
          }
    }
    public function adminAssLogin(Request $req){
        try{
            $req->validate([
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
                'password' => ['required', Rules\Password::defaults()],
            ]);
            if(!auth()->attempt(['email' => $req->email, 'password' => $req->password,'role'=>'adminAss']))
                return response()->json(['message'=>'password or email not correct'],422);
           $token=auth()->user()->createToken('admin',expiresAt:now()->addDays(4),abilities:['admin'])->plainTextToken;
           $adminData=new AssAdminLogInResource(auth()->user());
           return response()->json(['token'=>$token,'response'=>$adminData],200);
          } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
          }
    }
    //
    public function getAllAdminAss(Request $req){
            try{
                $numItems=$req->per_page??10;
                $data=AdminassResource::collection(User::where('role','adminAss')->paginate($numItems));
                return response()->json(['allAdminAss'=>$data],200);
            } catch(Exception $err){
                  return response()->json(["message"=>$err->getMessage()],500);
            }
    }
    public function getAllVolunteersWaiting(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=VolunteerResource::collection(Volunteer::where('isApproved','pin')->paginate($numItems));
            return response()->json(['allVolunteers'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllVolunteers(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=VolunteerResource::collection(Volunteer::where('isApproved','!=','pin')->paginate($numItems));
            return response()->json(['allVolunteers'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlanstoresWaiting(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=PlantsStoreResource::collection(Planstore::where('isApproved','pin')->paginate($numItems));
            return response()->json(['allPlanstores'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlanstores(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=PlantsStoreResource::collection(Planstore::where('isApproved','!=','pin')->paginate($numItems));
            return response()->json(['allPlanstores'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getPlanstoreTrees(Request $req,$planstore_id){
        try {
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $planstore_id))
                 return response()->json(["message"=>"id of planstore not correct"],422);
            $numItems=$req->per_page??10;
            $waiting_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','wait')->paginate($numItems));
            $done_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','done')->paginate($numItems));
            $false_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','false')->paginate($numItems));
            return response()->json([
                'waiting_trees'=>$waiting_trees,
                'done_trees'=>$done_trees,
                'false_trees'=>$false_trees
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function approvePlanOrVolun(Request $req){
        try{
            // { 
            //    approve:yes or no,
            //    type:plan or volun,
            //    id: ,
            //    rejectDesc:""
            // }
            $req->validate([
                'approve' => ['required','string','lowercase','max:3'],
                'type' => ['required','string','lowercase','max:5'],
                'id'=>['required','regex:/^([0-9]+)$/'],
                'rejectDesc'=>['nullable','string','max:500']
            ]);
            if($req->type=="plan"){
                $data=Planstore::find($req->id);
                if(!$data)
                     return response()->json(["message"=>"this planstore not found"],404);
                $data->isApproved=$req->approve;
                $data->rejectDesc=$req->rejectDesc;
                $data->adminApproved=auth()->user()->name;
                $data->save();
            } else if($req->type=="volun"){
                $data=Volunteer::find($req->id);
                if(!$data)
                     return response()->json(["message"=>"this volunteer not found"],404);
                $data->isApproved=$req->approve;
                $data->rejectDesc=$req->rejectDesc;
                $data->adminApproved=auth()->user()->name;
                $data->save();
            }
            return response()->json(["message"=>"operation success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAdvertisementsQue(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=AdvertisementsResource::collection(Advertisement::where('volunteer_id',null)->paginate($numItems));
            return response()->json(['allAdvertisements'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getWorksQue(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=WorkResource::collection(Work::where('volunteer_id',null)->paginate($numItems));
            return response()->json(['allWorks'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getWorks(Request $req){
        try {
            $numItems=$req->per_page??10;
            $waiting_works=WorkResource::collection(Work::where('status','wait')->paginate($numItems));
            $done_works=WorkResource::collection(Work::where('status','done')->paginate($numItems));
            $false_works=WorkResource::collection(Work::where('status','false')->paginate($numItems));
            return response()->json([
                'waiting_works'=>$waiting_works,
                'done_works'=>$done_works,
                'false_works'=>$false_works
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function assignToVolunteer(Request $req){
        try{
            // { 
            //    type:adv or work,
            //    volunteer_id: ,
            //    id:
            // }
            $req->validate([
                'type' => ['required','string','lowercase','max:5'],
                'volunteer_id'=>['required','regex:/^([0-9]+)$/'],
                'id'=>['required','regex:/^([0-9]+)$/']
            ]);
            if($req->type=="adv"){
                $data=Advertisement::find($req->id);
                if(!$data)
                     return response()->json(["message"=>"this advertisement not found"],404);
                $data->volunteer_id=$req->volunteer_id;
                $data->status="wait";
                $data->save();
            } else if($req->type=="work"){
                $data=Work::find($req->id);
                if(!$data)
                     return response()->json(["message"=>"this work not found"],404);
                $data->volunteer_id=$req->volunteer_id;
                $data->status="wait";
                $data->save();
            }
            return response()->json(["message"=>"operation success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getCategories(){
        try{
            $data=CategoryOnlyResource::collection(Category::get());
            return response()->json(['allCategories'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getArticlesOfCategory(Request $req,$cat_id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $cat_id))
                 return response()->json(["message"=>"id of category not correct"],422);
            $numItems=$req->per_page??10;  
            $data=PostResource::collection(Article::where('category_id',$cat_id)->paginate($numItems));
            return response()->json(['allArticles'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //
    public function createAdmin(AdminRequest $req){
         try{
            $imgName="no image";
            if($req->hasFile('logo'))
                  $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
            $user = User::create([
                'name' => $req->name,
                'email' => $req->email,
                'password' => Hash::make($req->string('password')),
                'logo'=>$imgName,
                'role'=>'admin'
            ]);
            $admin=new Admin();
            $admin->orgName=$req->orgName;
            $admin->desc=$req->desc;
            $admin->address=$req->address;
            $admin->phone=$req->phone;
            $user->admin()->save($admin);
            if($req->hasFile('imgs')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user->admin->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
         }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
         }
    }
    public function createAssAdmin(AdminRequest $req){
            try{
                $imgName="no image";
                if($req->hasFile('logo'))
                      $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $user = User::create([
                    'name' => $req->name,
                    'email' => $req->email,
                    'password' => Hash::make($req->string('password')),
                    'logo'=>$imgName,
                    'role'=>'adminAss',
                    'user_id'=>auth()->id()
                ]);
                $admin=new Admin();
                $admin->orgName=$req->orgName;
                $admin->desc=$req->desc;
                $admin->address=$req->address;
                $admin->phone=$req->phone;
                $user->admin()->save($admin);
                if($req->hasFile('imgs')){
                    $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                    $user->admin->images()->saveMany($paths);
                }
                return response()->json(["message"=>"create success"],201);
            }catch(Exception $err){
                return response()->json(["message"=>$err->getMessage()],500);
            }
    }
    public function deleteAssAdmin(string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of AssAdmin not correct"],422);
            $user=User::find($id);
            if(!$user)
                return response()->json(["message"=>"this user not found"],404);
            (new UploadImageController())->deleteLogoImage($user->logo);
            (new UploadImageController())->deleteMultiImage($user->images);
            $user->delete();          
            return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updateAssAdmin(UpdateAdminRequest $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of AssAdmin not correct"],422);
            $user=User::find($id);
            if(!$user)
                return response()->json(["message"=>"this AssAdmin not found"],404);
            $user->name=$req->name??$user->name;
            if($req->password)
                $user->password=Hash::make($req->string('password'))??$user->password;

            $user->admin->orgName=$req->orgName??$user->admin->orgName;
            $user->admin->desc=$req->desc??$user->admin->desc;
            $user->admin->address=$req->address??$user->admin->address;
            $user->admin->phone=$req->phone??$user->admin->phone;

            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $user->logo=$imgName;
            }
            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user->images);
                $user->images->destroy();
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user->admin->images()->saveMany($paths);
            }
            $user->admin->save();
            $user->save();
            return response()->json(["message"=>"update success"],200);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
}