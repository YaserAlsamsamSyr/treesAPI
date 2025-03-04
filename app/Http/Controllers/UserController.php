<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\WorkResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminassResource;
use App\Http\Resources\AdminResource;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\PlantsStoreResource;
use App\Http\Resources\AdvertisementsResource;
use App\Http\Resources\CategoryOnlyResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\EventResource;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\Event;
use App\Models\Volunteer;
use App\Models\Planstore;
use App\Models\Work;
use App\Models\User;
use App\Models\Traffic;
use App\Models\Day;
use App\Models\Month;
use App\Models\Year;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UploadImageController;
use App\Http\Requests\MacRequest;
use App\Http\Requests\VolunteerRequest;
use App\Http\Requests\PlantsStoreRequest;
use App\Http\Requests\WorkRequest;

class UserController extends Controller
{    
    public function treeQue(Request $req){
        try {
            $numItems=$req->per_page??10;
            $waiting_trees=AdvertisementsResource::collection(Advertisement::where('status','wait')->paginate($numItems));
            return response()->json([
                'waiting_trees'=>$waiting_trees
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function workQue(Request $req){
        try {
            $numItems=$req->per_page??10;
            $waiting_work=WorkResource::collection(Work::where('status','wait')->paginate($numItems));
            return response()->json([
                'waiting_work'=>$waiting_work
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlantsStores(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=PlantsStoreResource::collection(Planstore::where('isApproved','yes')->paginate($numItems));
            return response()->json(["allPlantsStore"=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //plan-id
    public function getPlanstoreTrees(Request $req,string $planstore_id){
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
    //
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
            else if($type=='art'){
                $data=Article::where('id',$id)->get();
                if(sizeof($data)==0)
                       return response()->json(['message'=>"this artricle not found"],404);
                $data=new PostResource($data[0]);
            }
            if(!$data)
                return response()->json(['message'=>"this type not found"],404);
            return response()->json($data,200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllVolunteers(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=VolunteerResource::collection(Volunteer::where('isApproved','yes')->paginate($numItems));
            return response()->json(['allVolunteers'=>$data],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }    
    public function getAllEvent(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=EventResource::collection(Event::paginate($numItems));
            return response()->json(['allEvents'=>$data],200);
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
    public function getArticlesOfCategory(Request $req,string $cat_id){
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
    public function getAllAdminAss(Request $req){
        try{
            $numItems=$req->per_page??10;
            $allassAdmin=AdminassResource::collection(User::where('role','adminAss')->paginate($numItems));
            $allAdmin=AdminassResource::collection(User::where('role','admin')->paginate($numItems));
            return response()->json(['allAdminAss'=>$allassAdmin,'allAdmin'=>$allAdmin],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function createWork(WorkRequest $req){
        try{
                $work = Work::create([
                    'name'=>$req->name,
                    'address'=>$req->address,
                    'desc'=>$req->desc,
                    'mac'=>$req->mac
                ]);
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $work->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function planstoreRequest(PlantsStoreRequest $req){
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
                    'role'=>'plan'
                ]);
            else
                $user = User::create([
                    'name' => $req->name,
                    'userName' => $req->userName,
                    'password' => Hash::make($req->string('password')),
                    'logo'=>$imgName,
                    'role'=>'plan'
                ]);
            $planstore=new Planstore();
            $planstore->mac=$req->mac;
            $planstore->ownerName=$req->ownerName??'';
            $planstore->desc=$req->desc??'';
            $planstore->address=$req->address??'';
            $planstore->phone=$req->phone??'';
            $planstore->openTime=$req->openTime??'';
            $planstore->closeTime=$req->closeTime??'';
            $planstore->isApproved="pin";
            $planstore->rejectDesc="";
            $planstore->adminApproved="";
            $user->planstore()->save($planstore);
            if($req->hasFile('imgs')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user->planstore->images()->saveMany($paths);
            }
            return response()->json(["message"=>"send success"],201);
       }catch(Exception $err){
             return response()->json(["message"=>$err->getMessage()],500);
       }
    }
    public function volunteerRequest(VolunteerRequest $req){
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
                    'role'=>'volun'
                ]);
            else
                $user = User::create([
                    'name' => $req->name,
                    'userName' => $req->userName,
                    'password' => Hash::make($req->string('password')),
                    'logo'=>$imgName,
                    'role'=>'volun'
                ]);
            $volun=new Volunteer();
            $volun->mac=$req->mac;
            $volun->desc=$req->desc??'';
            $volun->address=$req->address??'';
            $volun->phone=$req->phone??'';
            $volun->isApproved="pin";
            $volun->rejectDesc="";
            $volun->adminApproved="";
            $user->volunteer()->save($volun);
            return response()->json(["message"=>"send success"],201);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getMyWorks(Request $req){
        try {
            $mac=$req->mac;
            $pattern ="/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/";
            if(!preg_match($pattern, $mac))
                 return response()->json(["message"=>"mac not correct"],422);
            $numItems=$req->per_page??10;
            $waiting_works=WorkResource::collection(Work::where('mac',$mac)->where('status','wait')->paginate($numItems));
            $pindding_works=WorkResource::collection(Work::where('mac',$mac)->where('status','pin')->paginate($numItems));
            $done_works=WorkResource::collection(Work::where('mac',$mac)->where('status','done')->paginate($numItems));
            $false_works=WorkResource::collection(Work::where('mac',$mac)->where('status','false')->paginate($numItems));
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
    public function myRequests(Request $req){
        try{
            $mac=$req->mac;
            $pattern ="/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/";
            if(!preg_match($pattern, $mac))
                 return response()->json(["message"=>"mac not correct"],422);
            $numItems=$req->per_page??10;
            $plants=PlantsStoreResource::collection(Planstore::where('mac',$mac)->paginate($numItems));
            $volunteers=VolunteerResource::collection(Volunteer::where('mac',$mac)->paginate($numItems));
            return response()->json(['plantsStore'=>$plants,'volunteers'=>$volunteers],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function addUserToTraffic(MacRequest $req){
        try{
            $newTraffic=new Traffic();
            $newTraffic->mac=$req->mac;
            $day=date('d');
            $month=date('m');
            $year=date('y');
            $isFound=Traffic::where('mac',$req->mac)->first();
            if(!$isFound)//first time
                $newTraffic->firstTime=true;
            else
                $newTraffic->firstTime=false;
            $newTraffic->save();
            $isYear=Year::where('year',$year)->first();
            if(!$isYear){
                $isYear=Year::create(['year'=>$year]);
            }
            $isMonth=Month::where('month',$month)->first();
            if(!$isMonth){
                $isMonth=Month::create(['month'=>$month]);
            }
            if(!$isYear->months()->where('month',$month)->first())
                    $isMonth->years()->attach($isYear); 
                  
            $isday=Day::where('day',$day)->first();
            if(!$isday){
                $isday=Day::create(['day'=>$day]);
            }
            if(!$isMonth->days()->where('day',$day)->first())
                    $isMonth->days()->attach($isday); 
            $newTraffic->days()->attach($isday);
             return response()->json(['message'=>'added success'],201);
        } catch(Exception $err){
            return response()->json(['message'=>$err->getMessage(),422]);
        }
    } 
}
