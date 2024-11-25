<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 1:15 PM
 */
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'],function (){
    //TODO @fa 11/24/24 Language list
    Route::post('language-list',[App\Http\Controllers\Api\V1\LanguageController::class,'index']);
    Route::get('/', function (){
        return response()->json([
            'status' => true,
            'message' => 'Welcome to the API',
        ]);
    });
    Route::post('login',[App\Http\Controllers\Api\V1\Auth\AuthController::class,'login']);
    Route::post('forgot-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'forgotPassword']);
    Route::post('update-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'updatePassword']);
    Route::post('verify-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'verifyPassword']);
    Route::group(['prefix' => 'guideline'],function (){
        Route::post('list',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'guideLineList']);
        Route::post('details',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'guideLineDetails']);
    });
    Route::group(['prefix' => 'awareness'],function (){
        Route::post('list',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'awarenessList']);
        Route::post('details',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'awarenessDetails']);

    });
    Route::group(['prefix' => 'news'],function (){
        Route::post('all',[App\Http\Controllers\Api\V1\News\NewsController::class,'newsList']);
        Route::post('type',[App\Http\Controllers\Api\V1\News\NewsController::class,'type']);
    });
    Route::group(['prefix' => 'spot'],function (){
        Route::post('list',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'index']);
        Route::post('details',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'details']);
    });
    Route::group(['middleware' => 'auth:sanctum'],function (){
        Route::post('logout',[App\Http\Controllers\Api\V1\Auth\AuthController::class,'logout']);
        Route::post('avatar-upload', [\App\Http\Controllers\Api\V1\User\UserController::class, 'avatarUpload']);
        //TODO @fa 2022-11-30 password update
        Route::post('password-update', [\App\Http\Controllers\Api\V1\User\UserController::class, 'passwordUpdate']);

        //TODO @fa 11/24/24 guideline
        Route::group(['prefix' => 'guideline'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'storeGuideLine']);
            Route::post('update',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'updateGuideLine']);
        });
        //TODO @fa 11/24/24 awareness
        Route::group(['prefix' => 'awareness'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'awarenessStore']);
            Route::post('update',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'awarenessUpdate']);
        });
        //TODO @fa 2024-11-25 news
        Route::group(['prefix' => 'news'],function (){
            Route::post('list',[App\Http\Controllers\Api\V1\News\NewsController::class,'list']);
            Route::post('store',[App\Http\Controllers\Api\V1\News\NewsController::class,'store']);
            Route::post('publish',[App\Http\Controllers\Api\V1\News\NewsController::class,'publish']);
            Route::post('delete',[App\Http\Controllers\Api\V1\News\NewsController::class,'delete']);
        });
        //TODO @fa 2024-11-25 spot
        Route::group(['prefix' => 'spot'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'store']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'edit']);
            Route::post('update',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'update']);
            Route::post('delete',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'delete']);
        });
    });
});
