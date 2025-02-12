<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\WorkResource;
use App\Http\Resources\AdminResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminassResource;
use App\Http\Resources\TrafficResource;
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
use App\Models\Event;
use App\Models\User;
use App\Models\Year;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UploadImageController;
use App\Http\Requests\AdminRequest;
use App\Http\Requests\ArticleRequest;
use App\Http\Requests\AdvertisementsRequest;
use App\Http\Requests\EventRequest;
use App\Http\Requests\VolunteerRequest;
use App\Http\Requests\PlantsStoreRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Requests\UpdateVolunterRequest;
use App\Http\Requests\UpdatePlantsStoreRequest;
use App\Http\Requests\UpdateAdvertisementsRequest;
use App\Http\Resources\EventResource;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    //login
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
    //get
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
            $yes=VolunteerResource::collection(Volunteer::where('isApproved','yes')->paginate($numItems));
            $no=VolunteerResource::collection(Volunteer::where('isApproved','no')->paginate($numItems));
            return response()->json(['yesVolunteers'=>$yes,'noVolunteers'=>$no],200);
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
            $yes=PlantsStoreResource::collection(Planstore::where('isApproved','yes')->paginate($numItems));
            $no=PlantsStoreResource::collection(Planstore::where('isApproved','no')->paginate($numItems));
            return response()->json(['yesPlan'=>$yes,'noPlan'=>$no],200);
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
            $pinding_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','pin')->paginate($numItems));
            $waiting_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','wait')->paginate($numItems));
            $done_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','done')->paginate($numItems));
            $false_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',$planstore_id)->where('status','false')->paginate($numItems));
            return response()->json([
                'waiting_trees'=>$waiting_trees,
                'done_trees'=>$done_trees,
                'false_trees'=>$false_trees,
                'pinding_trees'=>$pinding_trees
            ],200);  
        } catch(Exception $err) {
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
            $pindding_works=WorkResource::collection(Work::where('status','pin')->paginate($numItems));
            $done_works=WorkResource::collection(Work::where('status','done')->paginate($numItems));
            $false_works=WorkResource::collection(Work::where('status','false')->paginate($numItems));
            return response()->json([
                'waiting_works'=>$waiting_works,
                'done_works'=>$done_works,
                'false_works'=>$false_works,
                'pindding_works'=>$pindding_works
            ],200);  
        } catch(Exception $err) {
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
    public function getEvents(Request $req){
        try {
            $mytime = Carbon::now();
            $all=Event::where('endDate','<=',$mytime->toDateString())->get();
            foreach($all as $i){
                (new UploadImageController())->deleteMultiImage($i->images);
                $i->delete();
            }
            $numItems=$req->per_page??10;
            $allEvents=EventResource::collection(Event::paginate($numItems));
            return response()->json(['allEvents'=>$allEvents],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getperson(string $id,string $type){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id not correct"],422);
            $pattern = "/^[A-Za-z|\s]+$/";
            if(!preg_match($pattern, $type))
                return response()->json(["message"=>"type not correct"],422);
            $data='';
            if($type=='admin'){
                $data=User::where('role','admin')->where('id',$id)->get();
                if(sizeof($data)==0)
                       return response()->json(['message'=>"this admin not found"],404);
                $data=new AdminResource($data[0]);
            }
            else if($type=='adminAss'){
                $data=User::where('role','adminAss')->where('id',$id)->get();
                if(sizeof($data)==0)
                       return response()->json(['message'=>"this assAdmin not found"],404);
                $data=new AdminassResource($data[0]);
            }
            else if($type=='volun'){
                $data=User::where('role','volun')->where('id',$id)->get();
                if(sizeof($data)==0)
                       return response()->json(['message'=>"this volunteer not found"],404);
                $data=new VolunteerResource($data[0]->volunteer);
            }
            else if($type=='plan'){
                $data=User::where('role','plan')->where('id',$id)->get();
                if(sizeof($data)==0)
                       return response()->json(['message'=>"this planstore not found"],404);
                $data=new PlantsStoreResource($data[0]->planstore);
            }
            else if($type=='event'){
                $data=Event::where('id',$id)->get();
                if(sizeof($data)==0)
                       return response()->json(['message'=>"this event not found"],404);
                $data=new EventResource($data[0]);
            }
            if(!$data)
                return response()->json(['message'=>"this type not found"],404);
            return response()->json($data,200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //approve and assign
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
                $data->status="pin";
                $data->save();
            } else if($req->type=="work"){
                $data=Work::find($req->id);
                if(!$data)
                     return response()->json(["message"=>"this work not found"],404);
                $data->volunteer_id=$req->volunteer_id;
                $data->status="pin";
                $data->save();
            }
            return response()->json(["message"=>"operation success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //admin
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
    public function updateAdmin(UpdateAdminRequest $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of admin not correct"],422);
            $user=User::where('role','admin')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this admin not found"],404);
            $user[0]->name=$req->name??$user[0]->name;
            if($req->password)
                $user[0]->password=Hash::make($req->string('password'))??$user[0]->password;

            $user[0]->admin->orgName=$req->orgName??$user[0]->admin->orgName;
            $user[0]->admin->desc=$req->desc??$user[0]->admin->desc;
            $user[0]->admin->address=$req->address??$user[0]->admin->address;
            $user[0]->admin->phone=$req->phone??$user[0]->admin->phone;

            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $user[0]->logo=$imgName;
            }
            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user[0]->admin->images);
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user[0]->admin->images()->saveMany($paths);
            }
            $user[0]->admin->save();
            $user[0]->save();
            return response()->json(["message"=>"update success"],200);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    // AssAdmin
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
            $user=User::where('role','adminAss')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this AssAdmin not found"],404);
            (new UploadImageController())->deleteLogoImage($user[0]->logo);
            (new UploadImageController())->deleteMultiImage($user[0]->images);
            $user[0]->delete();          
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
            $user=User::where('role','adminAss')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this AssAdmin not found"],404);
            $user[0]->name=$req->name??$user[0]->name;
            if($req->password)
                $user[0]->password=Hash::make($req->string('password'))??$user[0]->password;

            $user[0]->admin->orgName=$req->orgName??$user[0]->admin->orgName;
            $user[0]->admin->desc=$req->desc??$user[0]->admin->desc;
            $user[0]->admin->address=$req->address??$user[0]->admin->address;
            $user[0]->admin->phone=$req->phone??$user[0]->admin->phone;

            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $user[0]->logo=$imgName;
            }
            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user[0]->admin->images);
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user[0]->admin->images()->saveMany($paths);
            }
            $user[0]->admin->save();
            $user[0]->save();
            return response()->json(["message"=>"update success"],200);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //volunteer
    public function createVolunteer(VolunteerRequest $req){
        try{
            $imgName="no image";
            if($req->hasFile('logo'))
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
            if($req->email)
                $user = User::create([
                    'name' => $req->name,
                    'email' => $req->email,
                    'userName' => $req->userName,
                    'password' => Hash::make($req->string('password')),
                    'logo'=>$imgName,
                    'role'=>'volun',
                    'user_id'=>auth()->id()
                ]);
            else
                $user = User::create([
                    'name' => $req->name,
                    'userName' => $req->userName,
                    'password' => Hash::make($req->string('password')),
                    'logo'=>$imgName,
                    'role'=>'volun',
                    'user_id'=>auth()->id()
                ]);
            $volun=new Volunteer();
            $volun->mac="no mac";
            $volun->desc=$req->desc;
            $volun->address=$req->address;
            $volun->phone=$req->phone;
            $volun->isApproved="yes";
            $volun->rejectDesc="";
            $volun->adminApproved=auth()->user()->name;
            $user->volunteer()->save($volun);
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function deleteVolunteer(string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of volunteer not correct"],422);
            $user=User::where('role','volun')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this volunteer not found"],404);
            (new UploadImageController())->deleteLogoImage($user[0]->logo);
            $user[0]->delete();          
            return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updateVolunteer(UpdateVolunterRequest $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of volunteer not correct"],422);
            $user=User::where('role','volun')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this volunteer not found"],404);
            $user[0]->name=$req->name??$user[0]->name;
            $user[0]->email=$req->email??$user[0]->email;
            if($req->password)
                $user[0]->password=Hash::make($req->string('password'))??$user[0]->password;
            $user[0]->volunteer->desc=$req->desc??$user[0]->volunteer->desc;
            $user[0]->volunteer->address=$req->address??$user[0]->volunteer->address;
            $user[0]->volunteer->phone=$req->phone??$user[0]->volunteer->phone;
            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $user[0]->logo=$imgName;
            }
            $user[0]->volunteer->save();
            $user[0]->save();
            return response()->json(["message"=>"update success"],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //planstore
    public function createPlanstore(PlantsStoreRequest $req){
           try{
                $imgName="no image";
                if($req->hasFile('logo'))
                      $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                if($req->email)
                    $user = User::create([
                        'name' => $req->name,
                        'email' => $req->email,
                        'userName' => $req->userName,
                        'password' => Hash::make($req->string('password')),
                        'logo'=>$imgName,
                        'role'=>'plan',
                        'user_id'=>auth()->id()
                    ]);
                else
                    $user = User::create([
                        'name' => $req->name,
                        'userName' => $req->userName,
                        'password' => Hash::make($req->string('password')),
                        'logo'=>$imgName,
                        'role'=>'plan',
                        'user_id'=>auth()->id()
                    ]);
                $planstore=new Planstore();
                $planstore->mac="no mac";
                $planstore->ownerName=$req->ownerName;
                $planstore->desc=$req->desc;
                $planstore->address=$req->address;
                $planstore->phone=$req->phone;
                $planstore->openTime=$req->openTime;
                $planstore->closeTime=$req->closeTime;
                $planstore->isApproved="yes";
                $planstore->rejectDesc="";
                $planstore->adminApproved=auth()->user()->name;
                $user->planstore()->save($planstore);
                if($req->hasFile('imgs')){
                    $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                    $user->planstore->images()->saveMany($paths);
                }
                return response()->json(["message"=>"create success"],201);
           }catch(Exception $err){
                 return response()->json(["message"=>$err->getMessage()],500);
           }
    }
    public function deletePlanstore(string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of planstore not correct"],422);
            $user=User::where('role','plan')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this planstore not found"],404);
            (new UploadImageController())->deleteLogoImage($user[0]->logo);
            (new UploadImageController())->deleteMultiImage($user[0]->planstore->images);
            foreach ($user[0]->planstore->advertisements as $tree)
                if($tree->status=="متوفر")
                    $tree->status->delete();
            $user[0]->delete();          
            return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updatePlanstore(UpdatePlantsStoreRequest $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of planstore not correct"],422);
            $user=User::where('role','plan')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this planstore not found"],404);
            $user[0]->name=$req->name??$user[0]->name;
            $user[0]->email=$req->email??$user[0]->email;
            if($req->password)
                $user[0]->password=Hash::make($req->string('password'))??$user[0]->password;
            $user[0]->planstore->desc=$req->desc??$user[0]->planstore->desc;
            $user[0]->planstore->address=$req->address??$user[0]->planstore->address;
            $user[0]->planstore->phone=$req->phone??$user[0]->planstore->phone;
            $user[0]->planstore->ownerName=$req->ownerName??$user[0]->planstore->ownerName;
            $user[0]->planstore->openTime=$req->openTime??$user[0]->planstore->openTime;
            $user[0]->planstore->closeTime=$req->closeTime??$user[0]->planstore->closeTime;
            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $user[0]->logo=$imgName;
            }
            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user[0]->planstore->images);
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user[0]->planstore->images()->saveMany($paths);
            }
            $user[0]->planstore->save();
            $user[0]->save();
            return response()->json(["message"=>"update success"],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //tree
    public function createTree(AdvertisementsRequest $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of plantsstore not correct"],422);
            $user=User::where('role','plan')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this planstore not found"],404);
            $tree=new Advertisement();
            $tree->name = $req->name;
            $tree->plantsStoreName=$user[0]->name;
            $tree->desc = $req->desc;
            $tree->planstore_id=$user[0]->planstore->id;
            $tree->save();
            $user[0]->planstore->rate++;
            $user[0]->planstore->save();
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $tree->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function deleteTree(string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of tree not correct"],422);
           $tree=Advertisement::where('status','wait')->where('id',$id)->get();
           if(sizeof($tree)==0)
               return response()->json(["message"=>"this tree not found or can not delete it"],403);
           (new UploadImageController())->deleteMultiImage($tree[0]->images);
           $tree[0]->delete();          
           return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updateTree(UpdateAdvertisementsRequest $req,string $id){
         try{
             $pattern = "/^[0-9]+$/";
             if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of tree not correct"],422);
             $tree=Advertisement::where('status','wait')->where('id',$id)->get();
             if(sizeof($tree)==0)
                 return response()->json(["message"=>"this tree not found or can not update it"],404);
             $tree[0]->name=$req->name??$tree[0]->name;
             $tree[0]->desc=$req->desc??$tree[0]->desc;
             $tree[0]->plantsStoreName=$req->plantsStoreName??$tree[0]->plantsStoreName;
             if($req->hasFile('imgs')){
                 (new UploadImageController())->deleteMultiImage($tree[0]->images);
                 $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                 $tree[0]->images()->saveMany($paths);
             }
             $tree[0]->save();
             return response()->json(["message"=>"update success"],200);
         } catch(Exception $err){
             return response()->json(["message"=>$err->getMessage()],500);
         }
    }
    //category
    public function createcategory(Request $req){
        try{
            $pattern = "/^[A-Za-z|\s]+$/";
            if(!preg_match($pattern, $req->name))
                 return response()->json(["message"=>"name not correct"],422);
            $cat=new Category();
            $cat->name=$req->name;
            $cat->admin_id=auth()->user()->admin->id;
            $cat->save();
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function deleteCategory(string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of category not correct"],422);
           $cat=Category::where('id',$id)->get();
           if(sizeof($cat)==0)
               return response()->json(["message"=>"this category not found"],404);
            $cat[0]->delete();
           return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updateCategory(Request $req,string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of category not correct"],422);
           $pattern = "/^[A-Za-z|\s]+$/";
           if(!preg_match($pattern, $req->name))
                return response()->json(["message"=>"name not correct"],422);
           $cat=Category::where('id',$id)->get();
           if(sizeof($cat)==0)
               return response()->json(["message"=>"this category not found"],404);
           $cat[0]->name=$req->name;
           $cat[0]->save();
           return response()->json(["message"=>"update success"],200);
        } catch(Exception $err){
           return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //articles
    public function createArticles(ArticleRequest $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of category not correct"],422);
            $cat=Category::where('id',$id)->get();
            if(sizeof($cat)==0)
                return response()->json(["message"=>"this category not found"],404);
            $art=new Article();
            $art->name=$req->name;
            $art->title=$req->title;
            $art->desc=$req->desc;
            $art->category_id=$cat[0]->id;
            $art->save();
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $art->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function deleteArticles(string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of article not correct"],422);
           $art=Article::where('id',$id)->get();
           if(sizeof($art)==0)
               return response()->json(["message"=>"this article not found"],404);
            $art[0]->delete();
           return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    //traffic
    public function getTraffic(){
        try{
            return response()->json(['traffic'=>TrafficResource::collection(Year::all())],200);
        } catch(Exception $err){
            return response()->json(['message'=>$err->getMessage(),422]);
        }
    }
    //event
    public function deleteEvent(string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of event not correct"],422);
           $event=Event::where('id',$id)->get();
           if(sizeof($event)==0)
               return response()->json(["message"=>"this event not found"],404);
            (new UploadImageController())->deleteMultiImage($event[0]->images);
            $event[0]->delete();
           return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function createEvent(EventRequest $req){
        try{
            $event=new Event();
            $event->title=$req->title;
            $event->address=$req->address;
            $event->desc=$req->desc;
            $event->orgName=$req->orgName;
            $event->orgOwnerName=$req->orgOwnerName;
            $event->startDate=$req->startDate;
            $event->endDate=$req->endDate;
            $event->admin_id=auth()->id();
            $event->save();
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $event->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
}