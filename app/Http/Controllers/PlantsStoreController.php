<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantsStoreResource;
use App\Http\Resources\AdvertisementsResource;
use App\Http\Resources\VolunteerResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\AdminassResource;
use App\Http\Resources\CategoryOnlyResource;
use App\Http\Requests\AdvertisementsRequest;
use App\Http\Requests\UpdateAdvertisementsRequest;
use App\Models\Advertisement;
use App\Models\Volunteer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Models\Planstore;
use App\Models\Category;
use App\Models\Article;
use App\Models\User;
use Exception;

class PlantsStoreController extends Controller
{
    public function login(Request $req){
        try{
            $req->validate([
                'userName' => ['required', 'string', 'max:255'],
                'password' => ['required', Rules\Password::defaults()],
            ]);
            if(!auth()->attempt(['userName' => $req->userName, 'password' => $req->password,'role'=>'plan']))
                return response()->json(['message'=>'password or email not correct'],422);
            if(auth()->user->planstore->isApproved=="no")
               return response()->json(['message'=>'your request to join was rejected'],422);
            if(auth()->user->planstore->isApproved=="pin")
               return response()->json(['message'=>'your request to join is pindding'],422);
            $token=auth()->user()->createToken('plan',expiresAt:now()->addDays(4),abilities:['plan'])->plainTextToken;
            $planData=new PlantsStoreResource(auth()->user->planstore);
            return response()->json(['token'=>$token,$planData],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getMyTrees(Request $req){
        try {
            $numItems=$req->per_page??10;
            $pindding_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',auth()->user->planstore->id)->where('status','pin')->paginate($numItems));
            $waiting_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',auth()->user->planstore->id)->where('status','wait')->paginate($numItems));
            $done_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',auth()->user->planstore->id)->where('status','done')->paginate($numItems));
            $false_trees=AdvertisementsResource::collection(Advertisement::where('planstore_id',auth()->user->planstore->id)->where('status','false')->paginate($numItems));
            return response()->json([
                'waiting_trees'=>$waiting_trees,
                'done_trees'=>$done_trees,
                'false_trees'=>$false_trees,
                'pindding_trees'=>$pindding_trees
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getTree(string $tree_id){
        try {
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $tree_id))
                 return response()->json(["message"=>"id of tree not correct"],422);
            $tree=new AdvertisementsResource(Advertisement::find($tree_id));
            if(!$tree)
               return response()->json(['message'=>'this tree not found'],404);
            return response()->json($tree,200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function createTree(AdvertisementsRequest $req){
        try{
            $tree=new Advertisement();
            $tree->name = $req->name;
            $tree->plantsStoreName=auth()->user->name;
            $tree->desc = $req->desc;
            $tree->planstore_id=auth()->user->planstore->id;
            $tree->save();
            auth()->user->planstore->rate++;
            auth()->user->planstore->save();
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
           $tree=Advertisement::where('status','wait')->where('id',$id)->where('planstore_id',auth()->user->planstore->id)->get();
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
             $tree=Advertisement::where('status','wait')->where('id',$id)->where('planstore_id',auth()->user->planstore->id)->get();
             if(sizeof($tree)==0)
                 return response()->json(["message"=>"this tree not found or can not update it"],404);
             $tree[0]->name=$req->name??$tree[0]->name;
             $tree[0]->desc=$req->desc??$tree[0]->desc;
             $tree[0]->plantsStoreName=$req->plantsStoreName??$tree[0]->plantsStoreName;
             if($req->hasFile('imgs')){
                 (new UploadImageController())->deleteMultiImage($tree[0]->images);
                 $tree[0]->images->delete();
                 $paths=(new UploadImageController())->uploadMultiImages($req->file('imgs'));
                 $tree[0]->images()->saveMany($paths);
             }
             $tree[0]->save();
             return response()->json(["message"=>"update success"],200);
         } catch(Exception $err){
             return response()->json(["message"=>$err->getMessage()],500);
         }
    }
    public function assignTreeToVolunteer(Request $req){
        try{
            $req->validate([
                'volunteer_id'=>['required','regex:/^([0-9]+)$/'],
                'tree_id'=>['required','regex:/^([0-9]+)$/']
            ]);
            $data=Advertisement::find($req->tree_id);
            if(!$data)
                 return response()->json(["message"=>"this tree not found"],404);
            $data->volunteer_id=$req->volunteer_id;
            $data->status="pin";
            $data->save();
            return response()->json(["message"=>"operation success"],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllPlantsStores(Request $req){
        try{
            $numItems=$req->per_page??10;
            $data=PlantsStoreResource::collection(Planstore::where('isApproved','yes')->where('id','!=',auth()->user->planstore->id)->paginate($numItems));
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
            $data=VolunteerResource::collection(Volunteer::where('isApproved','yes')->paginate($numItems));
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
