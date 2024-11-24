<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 1:15 PM
 */
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'],function (){
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

    Route::group(['middleware' => 'auth:sanctum'],function (){
        Route::post('logout',[App\Http\Controllers\Api\V1\Auth\AuthController::class,'logout']);
        Route::post('avatar-upload', [\App\Http\Controllers\Api\V1\User\UserController::class, 'avatarUpload']);
        //TODO @fa 2022-11-30 password update
        Route::post('password-update', [\App\Http\Controllers\Api\V1\User\UserController::class, 'passwordUpdate']);
    });
});
