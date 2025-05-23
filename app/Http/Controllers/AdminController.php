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
use App\Http\Requests\CategoryRequest;
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
                $data=User::where('role','adminAss')->latest()->paginate($numItems);
                $adminass=AdminassResource::collection($data);
                $lastpage_adminass=$data->lastPage();
                return response()->json(['allAdminAss'=>$adminass,'lastpage_adminass'=>$lastpage_adminass],200);
            } catch(Exception $err){
                  return response()->json(["message"=>$err->getMessage()],500);
            }
    }
    public function getAllVolunteersWaiting(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Volunteer::where('isApproved','pin')->latest()->paginate($numItems);
            $volunteers=VolunteerResource::collection($data);
            $lastpage_volunteers=$data->lastPage();
            return response()->json(['allVolunteers'=>$volunteers,'lastpage_volunteers'=>$lastpage_volunteers],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllVolunteers(Request $req){
        try{
            $numItems=$req->per_page??10;
            $volun_yes=Volunteer::where('isApproved','yes')->latest()->paginate($numItems);
            $volun_no=Volunteer::where('isApproved','no')->latest()->paginate($numItems);
            $yes=VolunteerResource::collection($volun_yes);
            $no=VolunteerResource::collection($volun_no);
            $lastpage_noVolun=$volun_no->lastPage();
            $lastpage_yesVolun=$volun_yes->lastPage();
            return response()->json([
                'yesVolunteers'=>$yes,
                'noVolunteers'=>$no,
                'lastpage_yesVolun'=>$lastpage_yesVolun,
                'lastpage_noVolun'=>$lastpage_noVolun],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlanstoresWaiting(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Planstore::where('isApproved','pin')->latest()->paginate($numItems);
            $plans=PlantsStoreResource::collection($data);
            $lastpage_plans=$data->lastPage();
            return response()->json(['allPlanstores'=>$plans,'lastpage_plans'=>$lastpage_plans],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlanstores(Request $req){
        try{
            $numItems=$req->per_page??10;
            $yplants=Planstore::where('isApproved','yes')->latest()->paginate($numItems);
            $nplants=Planstore::where('isApproved','no')->latest()->paginate($numItems);
            $yes=PlantsStoreResource::collection($yplants);
            $no=PlantsStoreResource::collection($nplants);
            $lastpage_yplant=$yplants->lastPage();
            $lastpage_nplant=$nplants->lastPage();
            return response()->json([
                'yesPlan'=>$yes,
                'noPlan'=>$no,
                'lastpage_yplant'=>$lastpage_yplant,
                'lastpage_nplant'=>$lastpage_nplant
            ],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //---plan-id,volun-id
    public function getPlanstoreTrees(Request $req,string $id){ 
        try {
            $pattern = "/^(([0-9]+)|(-[0-9]+))$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of planstore not correct"],422);
            $numItems=$req->per_page??10;
            $ptrees='';
            $wtrees='';
            $dtrees='';
            $ftrees='';
            if($id!=-1) {
                $ptrees=Advertisement::where('planstore_id',$id)->where('status','pin')->latest()->paginate($numItems);
                $wtrees=Advertisement::where('planstore_id',$id)->where('status','wait')->latest()->paginate($numItems);
                $dtrees=Advertisement::where('planstore_id',$id)->where('status','done')->latest()->paginate($numItems);
                $ftrees=Advertisement::where('planstore_id',$id)->where('status','false')->latest()->paginate($numItems);
            } else{
                $ptrees=Advertisement::where('status','pin')->latest()->paginate($numItems);
                $wtrees=Advertisement::where('status','wait')->latest()->paginate($numItems);
                $dtrees=Advertisement::where('status','done')->latest()->paginate($numItems);
                $ftrees=Advertisement::where('status','false')->latest()->paginate($numItems);
            }
            $pinding_trees=AdvertisementsResource::collection($ptrees);
            $waiting_trees=AdvertisementsResource::collection($wtrees);
            $done_trees=AdvertisementsResource::collection($dtrees);
            $false_trees=AdvertisementsResource::collection($ftrees);
            
            $lastpage_ptrees=$ptrees->lastPage();
            $lastpage_wtrees=$wtrees->lastPage();
            $lastpage_dtrees=$dtrees->lastPage();
            $lastpage_ftrees=$ftrees->lastPage();
            
            return response()->json([
                'waiting_trees'=>$waiting_trees,
                'done_trees'=>$done_trees,
                'false_trees'=>$false_trees,
                'pinding_trees'=>$pinding_trees,
                'lastpage_ptrees'=>$lastpage_ptrees,
                'lastpage_wtrees'=>$lastpage_wtrees,
                'lastpage_dtrees'=>$lastpage_dtrees,
                'lastpage_ftrees'=>$lastpage_ftrees
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //
    public function getAdvertisementsQue(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Advertisement::where('volunteer_id',null)->latest()->paginate($numItems);
            $advs=AdvertisementsResource::collection($data);
            $lastpage_advs=$data->lastPage();
            return response()->json(['allAdvertisements'=>$advs,'lastpage_advs'=>$lastpage_advs],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getWorksQue(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Work::where('volunteer_id',null)->latest()->paginate($numItems);
            $works=WorkResource::collection($data);
            $lastpage_works=$data->lastPage();
            return response()->json(['allWorks'=>$works,'lastpage_works'=>$lastpage_works],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getWorks(Request $req){
        try {
            $numItems=$req->per_page??10;
            $wwork=Work::where('status','wait')->latest()->paginate($numItems);
            $pwork=Work::where('status','pin')->latest()->paginate($numItems);
            $dwork=Work::where('status','done')->latest()->paginate($numItems);
            $fwork=Work::where('status','false')->latest()->paginate($numItems);

            $waiting_works=WorkResource::collection($wwork);
            $pindding_works=WorkResource::collection($pwork);
            $done_works=WorkResource::collection($dwork);
            $false_works=WorkResource::collection($fwork);
            
            $lastpage_wwork=$wwork->lastPage();
            $lastpage_pwork=$pwork->lastPage();
            $lastpage_dwork=$dwork->lastPage();
            $lastpage_fwork=$fwork->lastPage();

            return response()->json([
                'waiting_works'=>$waiting_works,
                'done_works'=>$done_works,
                'false_works'=>$false_works,
                'pindding_works'=>$pindding_works,
                'lastpage_wwork'=>$lastpage_wwork,
                'lastpage_pwork'=>$lastpage_pwork,
                'lastpage_dwork'=>$lastpage_dwork,
                'lastpage_fwork'=>$lastpage_fwork
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
    public function getArticlesOfCategory(Request $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of category not correct"],422);
            $numItems=$req->per_page??10;
            $data=Article::where('category_id',$id)->latest()->paginate($numItems);
            $posts=PostResource::collection($data);
            $lastpage_posts=$data->lastPage();
            return response()->json(['allArticles'=>$posts,'lastpage_posts'=>$lastpage_posts],200);
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
            $data=Event::latest()->paginate($numItems);
            $allEvents=EventResource::collection($data);
            $lastpage_events=$data->lastPage();
            return response()->json(['allEvents'=>$allEvents,'lastpage_events'=>$lastpage_events],200);  
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
    //---plan-id,volun-id
    public function getvolunteerWorks(Request $req,string $id){
        try {
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of volunteer not correct"],422);
            $volunteer=Volunteer::where('isApproved','yes')->where('id',$id)->get();
            if(sizeof($volunteer)==0)
                return response()->json(['message'=>"this volunteer not found or not approved yet"],404);
            $numItems=$req->per_page??10;

            $qtrees=$volunteer[0]->advertisements()->where('status','pin')->latest()->paginate($numItems);
            $qwork=$volunteer[0]->works()->where('status','pin')->latest()->paginate($numItems);
            $ltrees=$volunteer[0]->advertisements()->where('status','false')->latest()->paginate($numItems);
            $lwork=$volunteer[0]->works()->where('status','false')->latest()->paginate($numItems);
            $dtree=$volunteer[0]->advertisements()->where('status','done')->latest()->paginate($numItems);
            $dwork=$volunteer[0]->works()->where('status','done')->latest()->paginate($numItems);

            $trees_Que=AdvertisementsResource::collection($qtrees);
            $works_Que=WorkResource::collection($qwork);
            $loadingTrees=AdvertisementsResource::collection($ltrees);
            $loadingWorks=WorkResource::collection($lwork);
            $doneTrees=AdvertisementsResource::collection($dtree);
            $doneWorks=WorkResource::collection($dwork);

            $lastpage_qtrees=$qtrees->lastPage();
            $lastpage_qwork=$qwork->lastPage();
            $lastpage_ltrees=$ltrees->lastPage();
            $lastpage_lwork=$lwork->lastPage();
            $lastpage_dtree=$dtree->lastPage();
            $lastpage_dwork=$dwork->lastPage();

            return response()->json([
                'trees_Que'=>$trees_Que,
                'works_Que'=>$works_Que,
                'loadingTrees'=>$loadingTrees,
                'loadingWorks'=>$loadingWorks,
                'doneTrees'=>$doneTrees,
                'doneWorks'=>$doneWorks,
                'lastpage_qtrees'=>$lastpage_qtrees,
                'lastpage_qwork'=>$lastpage_qwork,
                'lastpage_ltrees'=>$lastpage_ltrees,
                'lastpage_lwork'=>$lastpage_lwork,
                'lastpage_dtree'=>$lastpage_dtree,
                'lastpage_dwork'=>$lastpage_dwork
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //approve and assign ---plan-id,volun-id
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
    //
    public function createAdmin(AdminRequest $req){
         $userId='';
         $iamges=[];
         $logoImg='';
         try{
            $imgName="no image";
            if($req->hasFile('logo')){
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $logoImg=$imgName;
            }
            $user = User::create([
                'name' => $req->name,
                'email' => $req->email,
                'password' => Hash::make($req->string('password')),
                'logo'=>$imgName,
                'role'=>'admin'
            ]);
            $userId=$user->id;
            $admin=new Admin();
            $admin->orgName=$req->orgName;
            $admin->desc=$req->desc;
            $admin->address=$req->address;
            $admin->phone=$req->phone;
            $user->admin()->save($admin);
            if($req->hasFile('imgs')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $user->admin->images()->saveMany($paths);
                $iamges=$paths;
            }
            return response()->json(["message"=>"create success"],201);
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
    public function updateAdmin(UpdateAdminRequest $req,string $id){
        $iamges=[];
        $logoImg='';
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
            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $logoImg=$imgName;
                $user[0]->logo=$imgName;
            }
            $user[0]->save();
            $user[0]->admin->orgName=$req->orgName??$user[0]->admin->orgName;
            $user[0]->admin->desc=$req->desc??$user[0]->admin->desc;
            $user[0]->admin->address=$req->address??$user[0]->admin->address;
            $user[0]->admin->phone=$req->phone??$user[0]->admin->phone;
            $user[0]->admin->save();

            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user[0]->admin->images);
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $iamges=$paths;
                $user[0]->admin->images()->saveMany($paths);
            }
            return response()->json(["message"=>"update success"],200);
        }catch(Exception $err){
            if($logoImg!='')
                (new UploadImageController())->deleteLogoImage($logoImg);
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    // AssAdmin
    public function createAssAdmin(AdminRequest $req){
            $userId='';
            $iamges=[];
            $logoImg='';
            try{
                $imgName="no image";
                if($req->hasFile('logo')){
                    $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                    $logoImg=$imgName;
                }
                $user = User::create([
                    'name' => $req->name,
                    'email' => $req->email,
                    'password' => Hash::make($req->string('password')),
                    'logo'=>$imgName,
                    'role'=>'adminAss',
                    'user_id'=>auth()->id()
                ]);
                $userId=$user->id;
                $admin=new Admin();
                $admin->orgName=$req->orgName;
                $admin->desc=$req->desc;
                $admin->address=$req->address;
                $admin->phone=$req->phone;
                $user->admin()->save($admin);
                if($req->hasFile('imgs')){
                    $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                    $iamges=$paths;
                    $user->admin->images()->saveMany($paths);
                }
                return response()->json(["message"=>"create success"],201);
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
    public function deleteAssAdmin(string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of AssAdmin not correct"],422);
            $user=User::where('role','adminAss')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this AssAdmin not found"],404);
            (new UploadImageController())->deleteLogoImage($user[0]->logo);
            (new UploadImageController())->deleteMultiImage($user[0]->admin->images);
            foreach ($user[0]->admin->events as $event) {
                    $event->admin_id=null;
                    $event->save();
            }
            foreach ($user[0]->admin->categories as $category) {
                    $category->admin_id=null;
                    $category->save();
            }
            $user[0]->delete();          
            return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updateAssAdmin(UpdateAdminRequest $req,string $id){
        $iamges=[];
        $logoImg='';
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
             if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $logoImg=$imgName;
                $user[0]->logo=$imgName;
            }
            $user[0]->save();

            $user[0]->admin->orgName=$req->orgName??$user[0]->admin->orgName;
            $user[0]->admin->desc=$req->desc??$user[0]->admin->desc;
            $user[0]->admin->address=$req->address??$user[0]->admin->address;
            $user[0]->admin->phone=$req->phone??$user[0]->admin->phone;
            $user[0]->admin->save();
           
            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user[0]->admin->images);
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $iamges=$paths;
                $user[0]->admin->images()->saveMany($paths);
            }
            return response()->json(["message"=>"update success"],200);
        }catch(Exception $err){
            if($logoImg!='')
                (new UploadImageController())->deleteLogoImage($logoImg);
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //volunteer
    public function createVolunteer(VolunteerRequest $req){
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
            $userId=$user->id;
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
    public function deleteVolunteer(string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of volunteer not correct"],422);
            $user=User::where('role','volun')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this volunteer not found"],404);
            (new UploadImageController())->deleteLogoImage($user[0]->logo);
            foreach ($user[0]->volunteer->advertisements as $tree)
                if($tree->status=="done"||$tree->status=="false"){
                    (new UploadImageController())->deleteMultiImage($tree->images);
                    $tree->delete();
                } else if($tree->status=="pin"){
                    $tree->volunteer_id=null;
                    $tree->status="wait";
                    $tree->save();
                }
            foreach ($user[0]->volunteer->works as $work)
                if($work->status=="done"||$work->status=="false"){
                    (new UploadImageController())->deleteMultiImage($work->images);
                    $work->delete();
                } else if($work->status=="pin"){
                    $work->volunteer_id=null;
                    $work->status="wait";
                    $work->save();
                }
            $user[0]->delete();          
            return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updateVolunteer(UpdateVolunterRequest $req,string $id){
        $logoImg='';
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
            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $logoImg=$imgName;
                $user[0]->logo=$imgName;
            }
            $user[0]->save();

            $user[0]->volunteer->desc=$req->desc??$user[0]->volunteer->desc;
            $user[0]->volunteer->address=$req->address??$user[0]->volunteer->address;
            $user[0]->volunteer->phone=$req->phone??$user[0]->volunteer->phone;
            $user[0]->volunteer->save();

            return response()->json(["message"=>"update success"],200);
        } catch(Exception $err){
            if($logoImg!='')
                (new UploadImageController())->deleteLogoImage($logoImg);
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //planstore
    public function createPlanstore(PlantsStoreRequest $req){
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
                $userId=$user->id;
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
                    $iamges=$paths;
                    $user->planstore->images()->saveMany($paths);
                }
                return response()->json(["message"=>"create success"],201);
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
                if($tree->status=="wait"||$tree->status=="pin"){
                    (new UploadImageController())->deleteMultiImage($tree->images);
                    $tree->delete();
                } else{
                    $tree->planstore_id=null;
                    $tree->save();
                }
            $user[0]->delete();          
            return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    public function updatePlanstore(UpdatePlantsStoreRequest $req,string $id){
        $logoImg='';
        $iamges=[];
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
            if($req->hasFile('logo')){
                (new UploadImageController())->deleteLogoImage($user[0]->logo);
                $imgName=(new UploadImageController())->uploadeImage($req->file('logo'));
                $logoImg=$imgName;
                $user[0]->logo=$imgName;
            }
            $user[0]->save();

            $user[0]->planstore->desc=$req->desc??$user[0]->planstore->desc;
            $user[0]->planstore->address=$req->address??$user[0]->planstore->address;
            $user[0]->planstore->phone=$req->phone??$user[0]->planstore->phone;
            $user[0]->planstore->ownerName=$req->ownerName??$user[0]->planstore->ownerName;
            $user[0]->planstore->openTime=$req->openTime??$user[0]->planstore->openTime;
            $user[0]->planstore->closeTime=$req->closeTime??$user[0]->planstore->closeTime;
            $user[0]->planstore->save();
            
            if($req->hasFile('imgs')){
                (new UploadImageController())->deleteMultiImage($user[0]->planstore->images);
                $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                $iamges=$paths;
                $user[0]->planstore->images()->saveMany($paths);
            }
            return response()->json(["message"=>"update success"],200);
        } catch(Exception $err){
            if($logoImg!='')
                (new UploadImageController())->deleteLogoImage($logoImg);
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    //tree
    public function createTree(AdvertisementsRequest $req,string $id){
        $userId='';
        $treeId='';
        $iamges=[];
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of plantsstore not correct"],422);
            $user=User::where('role','plan')->where('id',$id)->get();
            if(sizeof($user)==0)
                return response()->json(["message"=>"this planstore not found"],404);
            $tree=new Advertisement();
            $tree->name=$req->name;
            $tree->desc=$req->desc;
            $tree->plantsStoreName=$user[0]->name;
            $tree->planstore_id=$user[0]->planstore->id;
            $tree->save();
            $treeId=$tree->id;
            $user[0]->planstore->rate++;
            $user[0]->planstore->save();
            $userId=$user[0]->id;
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $iamges=$paths;
                $tree->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
            if($userId!=''){
                $user=User::where('role','plan')->where('id',$id)->get();
                $user[0]->planstore->rate--;
                $user[0]->planstore->save();
            }
            if($treeId!=''){
                $tree=Advertisement::find($treeId);
                if($tree)
                    $tree->delete();
            }
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
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
        $iamges=[];
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
             $tree[0]->save();
             if($req->hasFile('imgs')){
                 (new UploadImageController())->deleteMultiImage($tree[0]->images);
                 $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                 $iamges=$paths;
                 $tree[0]->images()->saveMany($paths);
             }
             return response()->json(["message"=>"update success"],200);
         } catch(Exception $err){
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
            return response()->json(["message"=>$err->getMessage()],500);
         }
    }
    //category
    public function createcategory(CategoryRequest $req){
        try{
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
    public function updateCategory(CategoryRequest $req,string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of category not correct"],422);
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
    //work
    public function deleteWork(string $id){
        try{
           $pattern = "/^[0-9]+$/";
           if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of work not correct"],422);
           $work=Work::where('id',$id)->get();
           if(sizeof($work)==0)
               return response()->json(["message"=>"this work not found"],404);
            (new UploadImageController())->deleteMultiImage($work[0]->images);
            $work[0]->delete();
           return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    //articles
    public function createArticles(ArticleRequest $req,string $id){
        $iamges=[];
        $artId='';
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id of category not correct"],422);
            $cat=Category::where('id',$id)->get();
            if(sizeof($cat)==0)
                return response()->json(["message"=>"this category not found"],404);
            $art=new Article();
            $art->title=$req->title;
            $art->desc=$req->desc;
            $art->category_id=$cat[0]->id;
            $art->save();
            $artId=$art->id;
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $iamges=$paths;
                $art->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
            if($artId!='')
                $art=Article::find($artId);
                if($art)
                    $art->delete();
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
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
            (new UploadImageController())->deleteMultiImage($art[0]->images);
            $art[0]->delete();
           return response()->json(["message"=>"delete success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }  
    }
    //traffic
    public function getTraffic(){
        try{
            return response()->json(['traffic'=>TrafficResource::collection(Year::get())],200);
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
        $iamges=[];
        $eventId='';
        try{
            $event=Event::create([
                "title"=>$req->title,
                "address"=>$req->address,
                "desc"=>$req->desc,
                "orgName"=>$req->orgName,
                "orgOwnerName"=>$req->orgOwnerName,
                "startDate"=>$req->startDate,
                "endDate"=>$req->endDate
            ]);
            $eventId=$event->id;
            if($req->hasFile('images')){
                $paths=(new UploadImageController())->uploadMultiImages($req->file('images'));
                $iamges=$paths;
                $event->images()->saveMany($paths);
            }
            return response()->json(["message"=>"create success"],201);
        }catch(Exception $err){
            if($eventId!=''){
                $even=Event::find($eventId);
                if($even)
                    $even->delete();
            }
            if(sizeof($iamges)!=0)
                (new UploadImageController())->deleteMultiImagePaths($iamges);
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    // open tree or work to glopal
    public function openTreeOrWork(Request $req){
        try{
            $type=$req->type;
            $id=$req->id;
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id not correct"],422);
            if($type=="tree"){
                try{
                    $trees=Advertisement::where('status','pin')->where('id',$id)->get();
                } catch(Exception $err) {
                    $trees=Advertisement::where('status','pin')->where('id',$id)->get();
                }
                if(sizeof($trees)==0)
                    return response()->json(["message"=>"this tree not found"],404);
                $trees[0]->volunteer_id=null;
                $trees[0]->status="wait";
                $trees[0]->save();
            } else if($type=="work"){
                try{
                    $works=Work::where('status','pin')->where('id',$id)->get();
                } catch(Exception $err) {
                    $works=Work::where('status','pin')->where('id',$id)->get();
                }
                if(sizeof($works)==0)
                    return response()->json(["message"=>"this work not found"],404);
                $works[0]->volunteer_id=null;
                $works[0]->status="wait";
                $works[0]->save();
            }
            else
                return response()->json(["message"=>"type not correct"],422);
            return response()->json(["message"=>"operation success"],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }

    }
}