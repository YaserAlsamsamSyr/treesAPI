<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlantsStoreController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VolunteerController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return ['message' => "trees system"];
});

Route::middleware('allow')->group(function(){

    Route::get('/logout',function(Request $req){
        try{
            return $req->user()->currentAccessToken()->delete() ?
             response()->json(["message"=>"logout success"],200) :
             response()->json(["message"=>"logout fail"],422) ;
        }catch(Exception $err){
            return response()->json(["message"=>$err->getMessage()],500);
        }
    })->middleware('auth:sanctum');

    Route::post('/admin/login',[AdminController::class,'adminLogin']);
    Route::post('/admin/assAdmin/login',[AdminController::class,'adminAssLogin']);

    Route::middleware(['auth:sanctum','abilities:admin'])->prefix('admin')->group(function(){
       
        Route::get('/getAllAdminAss',[AdminController::class,'getAllAdminAss'])->middleware('isAdmin');
        Route::get('/getAllVolunteersWaiting',[AdminController::class,'getAllVolunteersWaiting']);
        Route::get('/getAllVolunteers',[AdminController::class,'getAllVolunteers']);
        Route::get('/getAllPlanstoresWaiting',[AdminController::class,'getAllPlanstoresWaiting']);
        Route::get('/getAllPlanstores',[AdminController::class,'getAllPlanstores']);
        Route::get('/getPlanstoreTrees/{id}',[AdminController::class,'getPlanstoreTrees']);
        Route::get('/getAdvertisementsQue',[AdminController::class,'getAdvertisementsQue']);
        Route::get('/getWorksQue',[AdminController::class,'getWorksQue']);
        Route::get('/getWorks',[AdminController::class,'getWorks']);
        Route::get('/getCategories',[AdminController::class,'getCategories']);
        Route::get('/getArticlesOfCategory/{id}',[AdminController::class,'getArticlesOfCategory']);
        Route::get('/getTraffic',[AdminController::class,'getTraffic']);
        Route::get('/getEvents',[AdminController::class,'getEvents']);
        Route::get('/getperson/{id}/{type}',[AdminController::class,'getperson']);
        Route::get('/getvolunteerWorks/{id}',[AdminController::class,'getvolunteerWorks']);

        Route::post('/approvePlanOrVolun',[AdminController::class,'approvePlanOrVolun']);
        Route::post('/assignToVolunteer',[AdminController::class,'assignToVolunteer']);
        Route::post('/createVolunteer',[AdminController::class,'createVolunteer']);
        Route::post('/createPlanstore',[AdminController::class,'createPlanstore']);
        Route::post('/createTree/{id}',[AdminController::class,'createTree']);
        Route::post('/createAdmin',[AdminController::class,'createAdmin'])->middleware('isAdmin');
        Route::post('/createAssAdmin',[AdminController::class,'createAssAdmin'])->middleware('isAdmin');
        Route::post('/updateAssAdmin/{id}',[AdminController::class,'updateAssAdmin']);
        Route::post('/updateAdmin/{id}',[AdminController::class,'updateAdmin'])->middleware('isAdmin');
        Route::post('/updateVolunteer/{id}',[AdminController::class,'updateVolunteer']);
        Route::post('/updatePlanstore/{id}',[AdminController::class,'updatePlanstore']);
        Route::post('/updateTree/{id}',[AdminController::class,'updateTree']);
        Route::post('/createEvent',[AdminController::class,'createEvent']);
        Route::post('/createcategory',[AdminController::class,'createcategory']);
        Route::post('/updateCategory/{id}',[AdminController::class,'updateCategory']);
        Route::post('/createArticles/{id}',[AdminController::class,'createArticles']);

        Route::delete('/deleteAssAdmin/{id}',[AdminController::class,'deleteAssAdmin'])->middleware('isAdmin');
        Route::delete('/deleteVolunteer/{id}',[AdminController::class,'deleteVolunteer']);
        Route::delete('/deletePlanstore/{id}',[AdminController::class,'deletePlanstore']);
        Route::delete('/deleteTree/{id}',[AdminController::class,'deleteTree']);
        Route::delete('/deleteEvent/{id}',[AdminController::class,'deleteEvent']);
        Route::delete('/deleteArticles/{id}',[AdminController::class,'deleteArticles']);
        Route::delete('/deleteCategory/{id}',[AdminController::class,'deleteCategory']);
        Route::delete('/deleteWork/{id}',[AdminController::class,'deleteWork']);
         
    });
    // plan
    Route::post('/plan/login',[PlantsStoreController::class,'login']);

    Route::middleware(['auth:sanctum','abilities:plan'])->prefix('plan')->group(function(){

        Route::get('/getMyTrees',[PlantsStoreController::class,'getMyTrees']);
        Route::get('/getTree/{id}',[PlantsStoreController::class,'getTree']);
        Route::get('/getAllPlantsStores',[PlantsStoreController::class,'getAllPlantsStores']);
        Route::get('/getPlanstoreTrees/{id}',[PlantsStoreController::class,'getPlanstoreTrees']);
        Route::get('/getAllVolunteers',[PlantsStoreController::class,'getAllVolunteers']);
        Route::get('/getCategories',[PlantsStoreController::class,'getCategories']);
        Route::get('/getArticlesOfCategory/{id}',[PlantsStoreController::class,'getArticlesOfCategory']);
        Route::get('/getAllAdminAss',[PlantsStoreController::class,'getAllAdminAss']);
        
        Route::post('/createTree',[PlantsStoreController::class,'createTree']);
        Route::post('/updateTree/{id}',[PlantsStoreController::class,'updateTree']);
        Route::post('/assignTreeToVolunteer',[PlantsStoreController::class,'assignTreeToVolunteer']);

        Route::delete('/deleteTree/{id}',[PlantsStoreController::class,'deleteTree']);
    });
    //volun
    Route::post('/volun/login',[VolunteerController::class,'login']);
    
    Route::middleware(['auth:sanctum','abilities:volun'])->prefix('volun')->group(function(){
        Route::get('/getMyWorks',[VolunteerController::class,'getMyWorks']);
        Route::get('/allTreesAndWorksQue',[VolunteerController::class,'allTreesAndWorksQue']);
        Route::get('/getArticlesOfCategory',[VolunteerController::class,'getArticlesOfCategory']);
        Route::get('/getAllAdminAss',[VolunteerController::class,'getAllAdminAss']);
        Route::get('/getAllPlantsStores',[VolunteerController::class,'getAllPlantsStores']);
        Route::get('/getPlanstoreTrees/{id}',[VolunteerController::class,'getPlanstoreTrees']);
        Route::get('/getAllVolunteers',[VolunteerController::class,'getAllVolunteers']);
        Route::get('/getCategories',[VolunteerController::class,'getCategories']);
        
        Route::post('/doneWorkOrTree/{id}',[VolunteerController::class,'doneWorkOrTree']);
        Route::post('/approveWorkOrTree/{id}',[VolunteerController::class,'approveWorkOrTree']);
        Route::post('/rejectWorkOrTree/{id}',[VolunteerController::class,'rejectWorkOrTree']);
        Route::post('/assignWorkOrTreeToMe/{id}',[VolunteerController::class,'assignWorkOrTreeToMe']);
    });
    // user
    Route::prefix('user')->group(function(){
        Route::get('/treeQue',[UserController::class,'treeQue']);
        Route::get('/workQue',[UserController::class,'workQue']);
        Route::get('/getAllPlantsStores',[UserController::class,'getAllPlantsStores']);
        Route::get('/getPlanstoreTrees/{id}',[UserController::class,'getPlanstoreTrees']);
        Route::get('/getAllVolunteers',[UserController::class,'getAllVolunteers']);
        Route::get('/getCategories',[UserController::class,'getCategories']);
        Route::get('/getArticlesOfCategory/{id}',[UserController::class,'getArticlesOfCategory']);
        Route::get('/getAllAdminAss',[UserController::class,'getAllAdminAss']);
        Route::get('/getperson/{id}/{type}',[UserController::class,'getperson']);
        Route::get('/getAllEvent',[UserController::class,'getAllEvent']);
        Route::get('/whatsNew',[UserController::class,'whatsNew']);
        Route::get('/totalAmount',[UserController::class,'totalAmount']);
        Route::get('/getVolunList',[UserController::class,'getVolunList']);

        Route::post('/createWork',[UserController::class,'createWork']);
        Route::post('/planstoreRequest',[UserController::class,'planstoreRequest']);
        Route::post('/volunteerRequest',[UserController::class,'volunteerRequest']);
        Route::post('/getMyWorks',[UserController::class,'getMyWorks']);
        Route::post('/myRequests',[UserController::class,'myRequests']);
        Route::post('/addUserToTraffic',[UserController::class,'addUserToTraffic']);
    });
});