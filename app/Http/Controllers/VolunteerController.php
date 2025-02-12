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
            return response()->json(['token'=>$token,$volData],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getMyWorks(Request $req){
        try {
            $numItems=$req->per_page??10;
            $trees_Que=AdvertisementsResource::collection(auth()->user()->volunteer->advertisements()->where('status','pin')->paginate($numItems));
            $works_Que=WorkResource::collection(auth()->user()->volunteer->works()->where('status','pin')->paginate($numItems));
            $loadingTrees=AdvertisementsResource::collection(auth()->user()->volunteer->advertisements()->where('status','false')->paginate($numItems));
            $loadingWorks=WorkResource::collection(auth()->user()->volunteer->works()->where('status','false')->paginate($numItems));
            $doneTrees=AdvertisementsResource::collection(auth()->user()->volunteer->advertisements()->where('status','done')->paginate($numItems));
            $doneWorks=WorkResource::collection(auth()->user()->volunteer->works()->where('status','done')->paginate($numItems));
            return response()->json([
                'trees_Que'=>$trees_Que,
                'works_Que'=>$works_Que,
                'loadingTrees'=>$loadingTrees,
                'loadingWorks'=>$loadingWorks,
                'doneTrees'=>$doneTrees,
                'doneWorks'=>$doneWorks
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
            $trees_Que=AdvertisementsResource::collection(Advertisement::where('status','wait')->paginate($numItems));
            $works_Que=WorkResource::collection(Work::where('status','wait')->paginate($numItems));
            return response()->json([
                'trees_Que'=>$trees_Que,
                'works_Que'=>$works_Que
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
    public function getAllVolunteers(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=VolunteerResource::collection(Volunteer::where('id','!=',auth()->user()->volunteer->id)->where('isApproved','yes')->paginate($numItems));
            return response()->json(['allVolunteers'=>$data],200);
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
}
