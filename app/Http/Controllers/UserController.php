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
use App\Http\Resources\VolunListResource;

class UserController extends Controller
{   
    public function treeQue(Request $req){
        try {
            $numItems=$req->per_page??10;
            $data=Advertisement::where('status','wait')->latest()->paginate($numItems);
            $waiting_trees=AdvertisementsResource::collection($data);
            // new
            $waiting_trees_lastPage=$data->lastPage();
            //
            return response()->json([
                'waiting_trees'=>$waiting_trees,
                'waiting_trees_lastpage'=>$waiting_trees_lastPage
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function workQue(Request $req){
        try {
            $numItems=$req->per_page??10;
            $data=Work::where('status','wait')->latest()->paginate($numItems);
            $waiting_work=WorkResource::collection($data);
            // new
            $lastPage_works=$data->lastPage();
            //
            return response()->json([
                'waiting_work'=>$waiting_work,
                'waiting_work_lastPage'=>$lastPage_works
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlantsStores(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Planstore::where('isApproved','yes')->latest()->paginate($numItems);
            $final=PlantsStoreResource::collection($data);
            // new
            $lastPage=$data->lastPage();
            //
            return response()->json(["allPlantsStore"=>$final,'planstores_lastPage'=>$lastPage],200);
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
            
            $ptrees=Advertisement::where('planstore_id',$planstore_id)->where('status','pin')->latest()->paginate($numItems);
            $wtrees=Advertisement::where('planstore_id',$planstore_id)->where('status','wait')->latest()->paginate($numItems);
            $dtrees=Advertisement::where('planstore_id',$planstore_id)->where('status','done')->latest()->paginate($numItems);
            $ftrees=Advertisement::where('planstore_id',$planstore_id)->where('status','false')->latest()->paginate($numItems);
            
            $pinding_trees=AdvertisementsResource::collection($ptrees);
            $waiting_trees=AdvertisementsResource::collection($wtrees);
            $done_trees=AdvertisementsResource::collection($dtrees);
            $false_trees=AdvertisementsResource::collection($ftrees);
            // new
            $lastpage_pinding_trees=$ptrees->lastPage();
            $lastpage_waiting_trees=$wtrees->lastPage();
            $lastpage_done_trees=$dtrees->lastPage();
            $lastpage_false_trees=$ftrees->lastPage();
            return response()->json([
                'waiting_trees'=>$waiting_trees,
                'done_trees'=>$done_trees,
                'false_trees'=>$false_trees,
                'pinding_trees'=>$pinding_trees,
                'lastpage_waiting_trees'=>$lastpage_waiting_trees,
                'lastpage_done_trees'=>$lastpage_done_trees,
                'lastpage_false_trees'=>$lastpage_false_trees,
                'lastpage_pinding_trees'=>$lastpage_pinding_trees
                
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
            $data=Volunteer::where('isApproved','yes')->latest()->paginate($numItems);
            $final=VolunteerResource::collection($data);
            // new
            $lastpage_volunters=$data->lastPage();
            return response()->json(['allVolunteers'=>$final,'lastpage_volunters'=>$lastpage_volunters],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }    
    public function getAllEvent(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Event::latest()->paginate($numItems);
            $final=EventResource::collection($data);
            // new
            $lastpage_events=$data->lastPage();
            return response()->json(['allEvents'=>$final,'lastpage_events'=>$lastpage_events],200);
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

            $data=Article::where('category_id',$cat_id)->latest()->paginate($numItems);
            $final=PostResource::collection($data);
            // new
            $lastpage_art=$data->lastPage();
            return response()->json(['allArticles'=>$final,'lastpage_art'=>$lastpage_art],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllAdminAss(Request $req){
        try{
            $numItems=$req->per_page??10;
            $adminData=User::where('role','admin')->latest()->paginate($numItems);
            $assAdminData=User::where('role','adminAss')->latest()->paginate($numItems);
            
            $allassAdmin=AdminassResource::collection($assAdminData);
            $allAdmin=AdminassResource::collection($adminData);
            // new
            $lastpage_assAdmin=$assAdminData->lastPage();
            $lastpage_admin=$adminData->lastPage();
            return response()->json([
                'allAdminAss'=>$allassAdmin,
                'allAdmin'=>$allAdmin,
                'lastpage_assAdmin'=>$lastpage_assAdmin,
                'lastpage_admin'=>$lastpage_admin
            ],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function createWork(WorkRequest $req){
        $workId='';
        $iamges=[];
        try{
                $work = Work::create([
                    'name'=>$req->name,
                    'address'=>$req->address,
                    'desc'=>$req->desc,
                    'mac'=>$req->mac
                ]);
                $workId=$work->id;
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $iamges=$paths;
                $work->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        } catch(Exception $err){
            if($workId!=''){
                $work=Work::find($workId);
                if($work)
                    $work->delete();
            }
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function planstoreRequest(PlantsStoreRequest $req){
           $userId='';
           $logoImg='';
           $iamges=[];
        try{
            $imgName="no image";
            if($req->hasFile('logo')){
                  $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                  $logoImg=$imgName;
            }
            $user='';
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
            $userId=$user->id;
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
                $iamges=$paths;
                $user->planstore->images()->saveMany($paths);
            }
            return response()->json(["message"=>"send success"],201);
       }catch(Exception $err){
            if($userId!=''){
                $user=User::find($userId);
                if($user)
                    $user->delete();
            }
            if($logoImg!='')
                (new UploadImageController())->deleteLogoImage($logoImg);
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
             return response()->json(["message"=>$err->getMessage()],500);
       }
    }
    public function volunteerRequest(VolunteerRequest $req){
        $userId='';
        $logoImg='';
        try{
            $imgName="no image";
            if($req->hasFile('logo')){
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $logoImg=$imgName;
            }
            $user='';
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
            $userId=$user->id;
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
            if($userId!=''){
                $user=User::find($userId);
                if($user)
                    $user->delete();
            }
            if($logoImg!='')
                (new UploadImageController())->deleteLogoImage($logoImg);
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getMyWorks(MacRequest $req){
        try {
            $mac=$req->mac;
            $numItems=$req->per_page??10;
            
            $wworks=Work::where('mac',$mac)->where('status','wait')->latest()->paginate($numItems);
            $pworks=Work::where('mac',$mac)->where('status','pin')->latest()->paginate($numItems);
            $dworks=Work::where('mac',$mac)->where('status','done')->latest()->paginate($numItems);
            $fworks=Work::where('mac',$mac)->where('status','false')->latest()->paginate($numItems);

            $waiting_works=WorkResource::collection($wworks);
            $pindding_works=WorkResource::collection($pworks);
            $done_works=WorkResource::collection($dworks);
            $false_works=WorkResource::collection($fworks);
            // new
            $lastpage_waiting_works=$wworks->lastPage();
            $lastpage_pindding_works=$pworks->lastPage();
            $lastpage_done_works=$dworks->lastPage();
            $lastpage_false_works=$fworks->lastPage();

            return response()->json([
                'waiting_works'=>$waiting_works,
                'done_works'=>$done_works,
                'false_works'=>$false_works,
                'pindding_works'=>$pindding_works,
                'lastpage_waiting_works'=>$lastpage_waiting_works,
                'lastpage_pindding_works'=>$lastpage_pindding_works,
                'lastpage_done_works'=>$lastpage_done_works,
                'lastpage_false_works'=>$lastpage_false_works,
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function myRequests(MacRequest $req){
        try{
            $mac=$req->mac;
            $numItems=$req->per_page??10;

            $pdata=Planstore::where('mac',$mac)->latest()->paginate($numItems);
            $vdata=Volunteer::where('mac',$mac)->latest()->paginate($numItems);
            
            $plants=PlantsStoreResource::collection($pdata);
            $volunteers=VolunteerResource::collection($vdata);
            
            $lastpage_plants=$pdata->lastPage();
            $lastpage_volunteers=$vdata->lastPage();
            return response()->json([
                'plantsStore'=>$plants,
                'volunteers'=>$volunteers,
                'lastpage_plants'=>$lastpage_plants,
                'lastpage_volunteers'=>$lastpage_volunteers
            ],200);
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
    public function whatsNew(){
        try{
            $doneWork = WorkResource::collection(Work::where('status','done')->orderBy('id', 'desc')->paginate(15));
            $notDoneWork = WorkResource::collection(Work::where('status','wait')->orderBy('id', 'desc')->paginate(15));
            $doneTree = AdvertisementsResource::collection(Advertisement::where('status','done')->orderBy('id', 'desc')->paginate(15));
            $notDoneTree = AdvertisementsResource::collection(Advertisement::where('status','wait')->orderBy('id', 'desc')->paginate(15));
            $articles = PostResource::collection(Article::orderBy('id', 'desc')->paginate(5));
            $volunteers = VolunteerResource::collection(Volunteer::where('isApproved','yes')->orderBy('id', 'desc')->paginate(3));
            return response()->json([
                'doneWork'=>$doneWork,
                'notDoneWork'=>$notDoneWork,
                'doneTree'=>$doneTree,
                'notDoneTree'=>$notDoneTree,
                'articles'=>$articles,
                'volunteers'=>$volunteers
            ],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function totalAmount(){
        try{
            $doneWork = Work::where('status','done')->count();
            $volunteers = Volunteer::count();
            $doneTree = Advertisement::where('status','done')->count();
            $planstores = Planstore::count();
            return response()->json([
                'doneWork'=>$doneWork,
                'volunteers'=>$volunteers,
                'doneTree'=>$doneTree,
                'planstores'=>$planstores
            ],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    // new
    public function getVolunList(){
        try{
            $volunlist=VolunListResource::collection(Volunteer::get());
            return response()->json([
                'volunList'=>$volunlist
            ],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
}
