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
            if(auth()->user()->planstore->isApproved=="no")
               return response()->json(['message'=>'your request to join was rejected'],422);
            if(auth()->user()->planstore->isApproved=="pin")
               return response()->json(['message'=>'your request to join is pindding'],422);
            $token=auth()->user()->createToken('plan',expiresAt:now()->addDays(4),abilities:['plan'])->plainTextToken;
            $planData=new PlantsStoreResource(auth()->user()->planstore);
            return response()->json(['token'=>$token,"profile"=>$planData],200);
        } catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getMyTrees(Request $req){
        try {
            $numItems=$req->per_page??10;
           
            $ptrees=Advertisement::where('planstore_id',auth()->user()->planstore->id)->where('status','pin')->latest()->paginate($numItems);
            $wtrees=Advertisement::where('planstore_id',auth()->user()->planstore->id)->where('status','wait')->latest()->paginate($numItems);
            $dtrees=Advertisement::where('planstore_id',auth()->user()->planstore->id)->where('status','done')->latest()->paginate($numItems);
            $ftrees=Advertisement::where('planstore_id',auth()->user()->planstore->id)->where('status','false')->latest()->paginate($numItems);

            $pindding_trees=AdvertisementsResource::collection($ptrees);
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
                'pindding_trees'=>$pindding_trees,
                'lastpage_ptrees'=>$lastpage_ptrees,
                'lastpage_wtrees'=>$lastpage_wtrees,
                'lastpage_dtrees'=>$lastpage_dtrees,
                'lastpage_ftrees'=>$lastpage_ftrees
            ],200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getTree(string $id){
        try {
            $pattern = "/^[0-9]+$/";
            if(!preg_match($pattern, $id))
                 return response()->json(["message"=>"id of tree not correct"],422);
            $tree=new AdvertisementsResource(Advertisement::find($id));
            if(!$tree)
               return response()->json(['message'=>'this tree not found'],404);
            return response()->json($tree,200);  
        } catch(Exception $err) {
            return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function createTree(AdvertisementsRequest $req){
        $userId='';
        $treeId='';
        $iamges=[];
        try{
            $tree=new Advertisement();
            $tree->name = $req->name;
            $tree->plantsStoreName=auth()->user()->name;
            $tree->desc = $req->desc;
            $tree->planstore_id=auth()->user()->planstore->id;
            $tree->save();
            $treeId=$tree->id;
            auth()->user()->planstore->rate++;
            auth()->user()->planstore->save();
            $userId=auth()->id();
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
           $tree=Advertisement::where('status','wait')->where('id',$id)->where('planstore_id',auth()->user()->planstore->id)->get();
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
             $tree=Advertisement::where('status','wait')->where('id',$id)->where('planstore_id',auth()->user->planstore->id)->get();
             if(sizeof($tree)==0)
                 return response()->json(["message"=>"this tree not found or can not update it"],404);
             $tree[0]->name=$req->name??$tree[0]->name;
             $tree[0]->desc=$req->desc??$tree[0]->desc;
             $tree[0]->plantsStoreName=$req->plantsStoreName??$tree[0]->plantsStoreName;
             $tree[0]->save();
             if($req->hasFile('imgs')){
                 (new UploadImageController())->deleteMultiImage($tree[0]->images);
                 $tree[0]->images->delete();
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
    //volun-id
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
    //
    public function getAllPlantsStores(Request $req){
        try{
            $numItems=$req->per_page??10;

            $data=Planstore::where('isApproved','yes')->where('id','!=',auth()->user->planstore->id)->latest()->paginate($numItems);

            $plants=PlantsStoreResource::collection($data);

            $lastpage_plants=$data->lastPage();
            
            return response()->json(["allPlantsStore"=>$plants,'lastpage_plants'=>$lastpage_plants],200);
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

            $pindding_trees=AdvertisementsResource::collection($ptrees);
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
                'pinding_trees'=>$pindding_trees,
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
            
            $data=Volunteer::where('isApproved','yes')->latest()->paginate($numItems);

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

            $lastpage_arts=$data->lastPage();
            
            return response()->json(['allArticles'=>$art,'lastpage_arts'=>$lastpage_arts],200);
        } catch(Exception $err){
              return response()->json(["message"=>$err->getMessage()],500);
        }
    }
    public function getAllAdminAss(Request $req){
        try{
            $numItems=$req->per_page??10;

            $assadmin=User::where('role','adminAss')->latest()->paginate($numItems);
            $admin=User::where('role','admin')->latest()->paginate($numItems);
            
            $allassAdmin=AdminassResource::collection($assadmin);
            $allAdmin=AdminassResource::collection($admin);

            $lastpage_admin=$admin->lastPage();
            $lastpage_assadmin=$assadmin->lastPage();

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