<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::middleware('allow')->prefix('admin')->group(function(){

    Route::post('/login',[AdminController::class,'adminLogin']);
    Route::post('/AssAdmin/login',[AdminController::class,'adminAssLogin']);

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

        Route::post('/approvePlanOrVolun',[AdminController::class,'approvePlanOrVolun']);
        Route::post('/assignToVolunteer',[AdminController::class,'assignToVolunteer']);
    });
});
