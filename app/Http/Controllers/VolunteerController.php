<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantsStoreResource;
use App\Http\Resources\AdvertisementsResource;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\AdminassResource;
use App\Http\Resources\CategoryOnlyResource;
use App\Http\Resources\WorkResource;
use App\Models\Advertisement;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Models\Planstore;
use App\Models\Category;
use App\Models\Article;
use App\Models\User;
use App\Models\Work;
use Exception;

class VolunteerController extends Controller
{
    public function login(Request $req){
        try{
            $req->validate([
                'userName' => ['required', 'string', 'max:255'],
                'password' => ['required', Rules\Password::defaults()],
            ]);
            if(!auth()->attempt(['userName' => $req->userName, 'password' => $req->password,'role'=>'volun']))
                return response()->json(['message'=>'password or email not correct'],422);
            if(auth()->user()->volunteer->isApproved=="no")
               return response()->json(['message'=>'your request to join was rejected'],422);
            if(auth()->user()->volunteer->isApproved=="pin")
               return response()->json(['message'=>'your request to join is pindding'],422);
            $token=auth()->user()->createToken('volun',expiresAt:now()->addDays(4),abilities:['volun'])->plainTextToken;
            $volData=new VolunteerResource(auth()->user()->volunteer);
            return response()->json(['token'=>$token,"profile"=>$volData],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getMyWorks(Request $req){
        try {
            $numItems=$req->per_page??10;
            
            $qtree=auth()->user()->volunteer->advertisements()->where('status','pin')->latest()->paginate($numItems);
            $ltree=auth()->user()->volunteer->advertisements()->where('status','false')->latest()->paginate($numItems);
            $dtree=auth()->user()->volunteer->advertisements()->where('status','done')->latest()->paginate($numItems);
            $qwork=auth()->user()->volunteer->works()->where('status','pin')->latest()->paginate($numItems);
            $lwork=auth()->user()->volunteer->works()->where('status','false')->latest()->paginate($numItems);
            $dwork=auth()->user()->volunteer->works()->where('status','done')->latest()->paginate($numItems);
            
            $trees_Que=AdvertisementsResource::collection($qtree);
            $works_Que=WorkResource::collection($qwork);
            $loadingTrees=AdvertisementsResource::collection($ltree);
            $loadingWorks=WorkResource::collection($lwork);
            $doneTrees=AdvertisementsResource::collection($dtree);
            $doneWorks=WorkResource::collection($dwork);

            $lastPage_qtree=$qtree->lastPage();
            $lastPage_ltree=$ltree->lastPage();
            $lastPage_dtree=$dtree->lastPage();
            $lastPage_qwork=$qwork->lastPage();
            $lastPage_lwork=$lwork->lastPage();
            $lastPage_dwork=$dwork->lastPage();

            return response()->json([
                'trees_Que'=>$trees_Que,
                'works_Que'=>$works_Que,
                'loadingTrees'=>$loadingTrees,
                'loadingWorks'=>$loadingWorks,
                'doneTrees'=>$doneTrees,
                'doneWorks'=>$doneWorks,
                'lastPage_qtree'=>$lastPage_qtree,
                'lastPage_ltree'=>$lastPage_ltree,
                'lastPage_dtree'=>$lastPage_dtree,
                'lastPage_qwork'=>$lastPage_qwork,
                'lastPage_lwork'=>$lastPage_lwork,
                'lastPage_dwork'=>$lastPage_dwork
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function doneWorkOrTree(Request $req,string $id){
        try{
            //type = tree or work
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id not correct"],422);
            $pattern = "/^[A-Za-z|\s]+$/";
            if(!preg_match($pattern, $req->type))
                return response()->json(["message"=>"type not correct"],422);
            if($req->type=="tree"){
                $tree=Advertisement::where('id',$id)->where('volunteer_id',auth()->user()->volunteer->id)->get();
                if(sizeof($tree)==0)
                    return response()->json(["message"=>"this tree not found"],404);
                $tree[0]->isDone='yes';
                $tree[0]->status='done';
                $tree[0]->save();
            } else if($req->type=="work"){
                $work=Work::where('id',$id)->where('volunteer_id',auth()->user()->volunteer->id)->get();
                if(sizeof($work)==0)
                    return response()->json(["message"=>"this work not found"],404);
                $work[0]->isDone='yes';
                $work[0]->status='done';
                $work[0]->save();
            } else
                return response()->json(["message"=>"type not found"],422);
            return response()->json(['message'=>'operation done'],200);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function approveWorkOrTree(Request $req,string $id){
        try{
            //type = tree or work
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id not correct"],422);
            $pattern = "/^[A-Za-z|\s]+$/";
            if(!preg_match($pattern, $req->type))
                return response()->json(["message"=>"type not correct"],422);
            if($req->type=="tree"){
                $tree=Advertisement::where('id',$id)->where('volunteer_id',auth()->user()->volunteer->id)->get();
                if(sizeof($tree)==0)
                    return response()->json(["message"=>"this tree not found"],404);
                $tree[0]->isDone='pin';
                $tree[0]->status='false';
                $tree[0]->save();
            } else if($req->type=="work"){
                $work=Work::where('id',$id)->where('volunteer_id',auth()->user()->volunteer->id)->get();
                if(sizeof($work)==0)
                    return response()->json(["message"=>"this work not found"],404);
                $work[0]->isDone='pin';
                $work[0]->status='false';
                $work[0]->save();
            } else
                return response()->json(["message"=>"type not found"],422);
            auth()->user()->volunteer->rate++;
            auth()->user()->volunteer->save();
            return response()->json(['message'=>'approved done'],200);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }   
    public function rejectWorkOrTree(Request $req,string $id){
        try{
            //type = tree or work
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id not correct"],422);
            $pattern = "/^[A-Za-z|\s]+$/";
            if(!preg_match($pattern, $req->type))
                return response()->json(["message"=>"type not correct"],422);
            if($req->type=="tree"){
                $tree=Advertisement::where('id',$id)->where('volunteer_id',auth()->user()->volunteer->id)->get();
                if(sizeof($tree)==0)
                    return response()->json(["message"=>"this tree not found"],404);
                $tree[0]->isDone='';
                $tree[0]->status='wait';
                $tree[0]->save();
            } else if($req->type=="work"){
                $work=Work::where('id',$id)->where('volunteer_id',auth()->user()->volunteer->id)->get();
                if(sizeof($work)==0)
                    return response()->json(["message"=>"this work not found"],404);
                $work[0]->isDone='';
                $work[0]->status='wait';
                $work[0]->save();
            } else
                return response()->json(["message"=>"type not found"],422);
            return response()->json(['message'=>'rejected done'],200);
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function assignWorkOrTreeToMe(Request $req,string $id){
        try{
            //type = tree or work
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                return response()->json(["message"=>"id not correct"],422);
            $pattern = "/^[A-Za-z|\s]+$/";
            if(!preg_match($pattern, $req->type))
                return response()->json(["message"=>"type not correct"],422);
            if($req->type=="tree"){
                $tree=Advertisement::where('id',$id)->get();
                if(sizeof($tree)==0)
                    return response()->json(["message"=>"this tree not found"],404);
                $tree[0]->isDone='pin';
                $tree[0]->status='false';
                $tree[0]->volunteer_id=auth()->user()->volunteer->id;
                $tree[0]->save();
            } else if($req->type=="work"){
                $work=Work::where('id',$id)->get();
                if(sizeof($work)==0)
                    return response()->json(["message"=>"this work not found"],404);
                $work[0]->isDone='pin';
                $work[0]->status='false';
                $work[0]->volunteer_id=auth()->user()->volunteer->id;
                $work[0]->save();
            } else
                return response()->json(["message"=>"type not found"],422);
            auth()->user()->volunteer->rate++;
            auth()->user()->volunteer->save();
            return response()->json(['message'=>'assigne done'],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function allTreesAndWorksQue(Request $req){
        try {
            $numItems=$req->per_page??10;

            $trees=Advertisement::where('status','wait')->latest()->paginate($numItems);
            $works=Work::where('status','wait')->latest()->paginate($numItems);

            $trees_Que=AdvertisementsResource::collection($trees);
            $works_Que=WorkResource::collection($works);

            $lastpage_trees=$trees->lastPage();
            $lastpage_works=$works->lastPage();

            return response()->json([
                'trees_Que'=>$trees_Que,
                'works_Que'=>$works_Que,
                'lastpage_trees'=>$lastpage_trees,
                'lastpage_works'=>$lastpage_works
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    } 
    public function getAllPlantsStores(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Planstore::where('isApproved','yes')->latest()->paginate($numItems);

            $plans=PlantsStoreResource::collection($data);
            
            $lastpage_plans=$data->lastPage();
            return response()->json(["allPlantsStore"=>$plans,'lastpage_plans'=>$lastpage_plans],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    // plan-id
    public function getPlanstoreTrees(Request $req,string $id){
        try {
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of planstore not correct"],422);
            $numItems=$req->per_page??10;

            $ptrees=Advertisement::where('planstore_id',$id)->where('status','pin')->latest()->paginate($numItems);
            $wtrees=Advertisement::where('planstore_id',$id)->where('status','wait')->latest()->paginate($numItems);
            $dtrees=Advertisement::where('planstore_id',$id)->where('status','done')->latest()->paginate($numItems);
            $ftrees=Advertisement::where('planstore_id',$id)->where('status','false')->latest()->paginate($numItems);

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
    public function getAllVolunteers(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=Volunteer::where('id','!=',auth()->user()->volunteer->id)->where('isApproved','yes')->latest()->paginate($numItems);
            
            $volunteers=VolunteerResource::collection($data);
            
            $lastpage_volunteers=$data->lastPage();

            return response()->json(['allVolunteers'=>$volunteers,'lastpage_volunteers'=>$lastpage_volunteers],200);
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
    public function getArticlesOfCategory(Request $req,string $id){
        try{
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of category not correct"],422);
            $numItems=$req->per_page??10;  

            $data=Article::where('category_id',$id)->latest()->paginate($numItems);

            $art=PostResource::collection($data);

            $lastpage_art=$data->lastPage();
            
            return response()->json(['allArticles'=>$art,'lastpage_art'=>$lastpage_art],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllAdminAss(Request $req){
        try{
            $numItems=$req->per_page??10;

            $assAdmin=User::where('role','adminAss')->latest()->paginate($numItems);
            $admin=User::where('role','admin')->latest()->paginate($numItems);

            $allassAdmin=AdminassResource::collection($assAdmin);
            $allAdmin=AdminassResource::collection($admin);

            $lastpage_admin=$admin->lastPage();
            $lastpage_assadmin=$assAdmin->lastPage();

            return response()->json([
                'allAdminAss'=>$allassAdmin,
                'allAdmin'=>$allAdmin,
                'lastpage_admin'=>$lastpage_admin,
                'lastpage_assadmin'=>$lastpage_assadmin
            ],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
}
