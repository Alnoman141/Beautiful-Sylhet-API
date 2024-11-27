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
    Route::post('login',[App\Http\Controllers\Api\V1\Auth\AuthController::class,'login'])->name('login');
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
    Route::group(['prefix' => 'locker'],function (){
        Route::post('list',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'index']);
    });
    //TODO @fa 2024-11-25 hotel
    Route::group(['prefix' => 'hotel'],function (){
        Route::post('list',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelList']);
        Route::post('details',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'details']);

        //TODO @fa 2024-11-25 hotel type list
        Route::post('type-list',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelTypeList']);
        //TODO @fa 2024-11-25 hotel category list
        Route::post('category-list',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelCategoryList']);
    });
    Route::group(['middleware' => 'auth:sanctum'],function (){
        Route::post('logout',[App\Http\Controllers\Api\V1\Auth\AuthController::class,'logout']);
        Route::post('avatar-upload', [\App\Http\Controllers\Api\V1\User\UserController::class, 'avatarUpload']);
        //TODO @fa 2022-11-30 password update
        Route::post('password-update', [\App\Http\Controllers\Api\V1\User\UserController::class, 'passwordUpdate']);

        //TODO @fa 11/24/24 guideline
        Route::group(['prefix' => 'guideline'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'storeGuideLine']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'editGuideLine']);
            Route::post('update',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'updateGuideLine']);
        });
        //TODO @fa 11/24/24 awareness
        Route::group(['prefix' => 'awareness'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'awarenessStore']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'editAwareness']);
            Route::post('update',[App\Http\Controllers\Api\V1\Tourist\TouristController::class,'awarenessUpdate']);
        });
        //TODO @fa 2024-11-25 news
        Route::group(['prefix' => 'news'],function (){
            Route::post('list',[App\Http\Controllers\Api\V1\News\NewsController::class,'list']);
            Route::post('store',[App\Http\Controllers\Api\V1\News\NewsController::class,'store']);
            Route::post('edit',[App\Http\Controllers\Api\V1\News\NewsController::class,'edit']);
            Route::post('publish',[App\Http\Controllers\Api\V1\News\NewsController::class,'publish']);
            Route::post('delete',[App\Http\Controllers\Api\V1\News\NewsController::class,'delete']);
        });
        //TODO @fa 2024-11-25 spot
        Route::group(['prefix' => 'spot'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'store']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'edit']);
            Route::post('update',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'update']);
            Route::post('delete',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'delete']);
            Route::post('dropdown',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'dropdown']);
            Route::post('delete-images',[App\Http\Controllers\Api\V1\Tourist\SpotController::class,'deleteImages']);
        });
        //TODO @fa 2024-11-25 locker
        Route::group(['prefix' => 'locker'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'store']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'edit']);
            Route::post('update',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'update']);
            Route::post('delete',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'delete']);
            Route::post('dropdown',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'dropdown']);

            Route::post('introduction',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'introduction']);
            Route::post('introduction-store',[App\Http\Controllers\Api\V1\Locker\LockerController::class,'introductionStore']);
        });
        //TODO @fa 2024-11-25 hotel
        Route::group(['prefix' => 'hotel'],function (){
            Route::post('store',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelStore']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelEdit']);
            Route::post('update',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelUpdate']);
            Route::post('delete',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelDelete']);

            Route::post('type-dropdown',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'typeDropdown']);
            Route::post('category-dropdown',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'categoryDropdown']);

            //TODO @fa 2024-11-25 hotel type
            Route::post('type-store',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelTypeStore']);
            Route::post('type-edit',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelTypeEdit']);
            Route::post('type-update',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelTypeUpdate']);
            Route::post('type-delete',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelTypeDelete']);

            //TODO @fa 2024-11-25 category
            Route::post('category-store',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelCategoryStore']);
            Route::post('category-edit',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelCategoryEdit']);
            Route::post('category-update',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelCategoryUpdate']);
            Route::post('category-delete',[App\Http\Controllers\Api\V1\Hotel\HotelController::class,'hotelCategoryDelete']);
        });

        //TODO @fa 2024-11-26 transport
        Route::group(['prefix' => 'transport'],function (){
            Route::post('type',[App\Http\Controllers\Api\V1\Transport\TransportController::class,'transportType']);
            Route::post('list',[App\Http\Controllers\Api\V1\Transport\TransportController::class,'index']);
            Route::post('store',[App\Http\Controllers\Api\V1\Transport\TransportController::class,'store']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Transport\TransportController::class,'edit']);
            Route::post('update',[App\Http\Controllers\Api\V1\Transport\TransportController::class,'update']);
            Route::post('delete',[App\Http\Controllers\Api\V1\Transport\TransportController::class,'delete']);
        });

        //TODO @fa 2024-11-26 event
        Route::group(['prefix' => 'event'],function (){
            Route::post('list',[App\Http\Controllers\Api\V1\Event\EventController::class,'index']);
            Route::post('store',[App\Http\Controllers\Api\V1\Event\EventController::class,'store']);
            Route::post('details',[App\Http\Controllers\Api\V1\Event\EventController::class,'show']);
            Route::post('edit',[App\Http\Controllers\Api\V1\Event\EventController::class,'edit']);
            Route::post('update',[App\Http\Controllers\Api\V1\Event\EventController::class,'update']);
            Route::post('delete',[App\Http\Controllers\Api\V1\Event\EventController::class,'destroy']);
        });
    });
});
