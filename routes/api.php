<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return ['message' => "trees system"];
});

Route::middleware('allow')->prefix('admin')->group(function(){
    
    Route::post('/login',[AdminController::class,'adminLogin']);
    Route::post('/assAdmin/login',[AdminController::class,'adminAssLogin']);

    Route::middleware(['auth:sanctum','abilities:admin'])->group(function(){
       
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

        Route::post('/approvePlanOrVolun',[AdminController::class,'approvePlanOrVolun']);
        Route::post('/assignToVolunteer',[AdminController::class,'assignToVolunteer']);
        Route::post('/createVolunteer',[AdminController::class,'createVolunteer']);
        Route::post('/createPlanstore',[AdminController::class,'createPlanstore']);
        Route::post('/createTree/{id}',[AdminController::class,'createTree']);
        Route::post('/createAdmin',[AdminController::class,'createAdmin'])->middleware('isAdmin');
        Route::post('/createAssAdmin',[AdminController::class,'createAssAdmin'])->middleware('isAdmin');
        Route::post('/updateAssAdmin/{id}',[AdminController::class,'updateAssAdmin'])->middleware('isAdmin');
        Route::post('/updateVolunteer/{id}',[AdminController::class,'updateVolunteer']);
        Route::post('/updatePlanstore/{id}',[AdminController::class,'updatePlanstore']);
        Route::post('/updateTree/{id}',[AdminController::class,'updateTree']);
        Route::post('/createEvent',[AdminController::class,'createEvent']);

        Route::delete('/deleteAssAdmin/{id}',[AdminController::class,'deleteAssAdmin'])->middleware('isAdmin');
        Route::delete('/deleteVolunteer/{id}',[AdminController::class,'deleteVolunteer']);
        Route::delete('/deletePlanstore/{id}',[AdminController::class,'deletePlanstore']);
        Route::delete('/deleteTree/{id}',[AdminController::class,'deleteTree']);
        Route::delete('/deleteEvent/{id}',[AdminController::class,'deleteEvent']);

    });
});
