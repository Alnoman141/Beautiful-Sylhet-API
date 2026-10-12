<?php
/**
 *Name: Abdullah Al Noman
 *Designation: Sr. Software Engineer
 *Date: 11/24/24 1:15 PM
 */

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    //TODO @fa 11/24/24 Language list
    Route::post('language-list', [App\Http\Controllers\Api\V1\LanguageController::class, 'index']);
    Route::get('/disk', function () {
        \App\Jobs\WeatherForecastJob::dispatch('forecast')->delay(now()->addSeconds(2));
    });
    Route::post('app-info', [\App\Http\Controllers\Api\V1\VisitorController::class, 'appInformation']);
    Route::get('/', function () {
        return response()->json([
            'status' => true,
            'message' => 'Welcome to the API',
        ]);
    });
    //TODO @fa 2025-03-02 authentication for tourist
    Route::group(['prefix' => 'tourist'], function () {
        Route::post('register', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'register']);
        //TODO @fa 2025-03-20 verify user
        Route::post('verify-user', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'verifyUser']);
        //TODO @fa 2025-03-20 verify token
        Route::post('verify-otp', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'verifyToken']);
        Route::post('login', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'login']);
        Route::post('verify', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'verify']);
        Route::post('otp-verify', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'otpVerify']);
        Route::post('resend-otp', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'resendOtp']);
    });
    //TODO @fa 2025-03-18 location
    Route::group(['prefix' => 'location'], function () {
        Route::post('division', [App\Http\Controllers\Api\V2\Location\LocationController::class, 'division']);
        Route::post('district', [App\Http\Controllers\Api\V2\Location\LocationController::class, 'district']);
        Route::post('upazila', [App\Http\Controllers\Api\V2\Location\LocationController::class, 'upazila']);
    });
    Route::post('login', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'forgotPassword']);
    Route::post('update-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'updatePassword']);
    Route::post('verify-password', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'verifyPassword']);
    Route::post('visitor', [App\Http\Controllers\Api\V1\VisitorController::class, 'store']);
    Route::post('dashboard', [App\Http\Controllers\Api\V1\VisitorController::class, 'index']);
    Route::post('country', [App\Http\Controllers\Api\V2\Location\LocationController::class, 'country']);
    Route::post('tour-plan', [App\Http\Controllers\Api\V2\AI\TourPlanGenerateController::class, 'tourPlan'])->middleware('throttle:tour-plan');
    Route::post('weather-info', function () {
        return response()->json([
            'status_code' => 200,
            'message' => 'Weather information',
            'data' => \Illuminate\Support\Facades\Cache::get('forecast') ?? \Illuminate\Support\Facades\Cache::get('tide-forecast'),
        ]);
    });
    Route::group(['prefix' => 'guideline'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'guideLineList']);
        Route::post('details', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'guideLineDetails']);
    });
    Route::group(['prefix' => 'awareness'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'awarenessList']);
        Route::post('details', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'awarenessDetails']);

    });
    Route::group(['prefix' => 'news'], function () {
        Route::post('all', [App\Http\Controllers\Api\V1\News\NewsController::class, 'newsList']);
        Route::post('type', [App\Http\Controllers\Api\V1\News\NewsController::class, 'type']);
    });
    Route::group(['prefix' => 'spot'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'details']);
    });
    Route::group(['prefix' => 'locker'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'index']);
    });
    //TODO @fa 2024-11-25 hotel
    Route::group(['prefix' => 'hotel'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelList']);
        Route::post('details', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'details']);

        //TODO @fa 2024-11-25 hotel type list
        Route::post('type-list', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelTypeList']);
        //TODO @fa 2024-11-25 hotel category list
        Route::post('category-list', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelCategoryList']);
    });
    //TODO @fa 2024-11-27 event
    Route::group(['prefix' => 'event'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Event\EventController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\Event\EventController::class, 'show']);
    });
    //TODO @fa 2024-11-27 campaign
    Route::group(['prefix' => 'campaign'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'show']);
    });
    Route::group(['prefix' => 'cox-bazaar'], function () {
        Route::post('details', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'coxBazaar']);
    });

    //TODO @fa 2024-11-28 bank
    Route::group(['prefix' => 'bank'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankList']);
        Route::post('details', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankDetails']);
        Route::post('dropdown', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankDropdown']);
    });
    //TODO @fa 2024-11-28 atm
    Route::group(['prefix' => 'atm'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'atmList']);
        Route::post('details', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'atmDetails']);
    });
    //TODO @fa 2024-11-28 red zone
    Route::group(['prefix' => 'red-zone'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'show']);
        Route::group(['prefix' => 'category'], function () {
            Route::post('list', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'categoryList']);
        });
    });
    //TODO @fa 2024-11-28 police
    Route::group(['prefix' => 'police'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Tourist\PoliceController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\Tourist\PoliceController::class, 'show']);
    });
    //TODO @fa 2024-11-28 doctor
    Route::group(['prefix' => 'doctor'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Doctor\DoctorController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\Doctor\DoctorController::class, 'show']);
    });
    //TODO @fa 2024-11-28 life guard
    Route::group(['prefix' => 'life-guard'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\LifeGuard\LifeGuardController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\LifeGuard\LifeGuardController::class, 'show']);
    });
    //TODO @fa 2024-12-03 transport
    Route::group(['prefix' => 'transport'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'index']);
        Route::post('type', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'transportType']);
        Route::post('details', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'show']);
    });
    //TODO @fa 2024-11-28 beach volunteer
    Route::group(['prefix' => 'beach-volunteer'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\BeachVolunteer\BeachVolunteerController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\BeachVolunteer\BeachVolunteerController::class, 'show']);
    });
    //TODO @fa 2024-11-28 notice
    Route::group(['prefix' => 'notice'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Notice\NoticeController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V1\Notice\NoticeController::class, 'show']);
    });
    //TODO @fa 2024-12-19 service
    Route::group(['prefix' => 'service'], function () {
        Route::post('list', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'index']);
    });
    Route::group(['prefix' => 'restaurant'], function () {
        Route::post('list', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'show']);
        Route::group(['prefix' => 'category'], function () {
            Route::post('list', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'categoryList']);
        });
    });

    //TODO @fa 2025-02-18 version V2 API
    Route::group(['prefix' => 'fair-board'], function () {
        Route::post('list', [App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class, 'index']);
        Route::post('details', [App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class, 'show']);
    });

    //TODO @fa 2025-02-18 version V3 API
    Route::group(['prefix' => 'tour-operator'], function () {
        Route::post('get-list', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'fetchTourOperator']);
        Route::post('details', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'fetchTourOperatorDetails']);
    });

    //TODO @fa 2025-02-18 version V3 API
    Route::group(['prefix' => 'tour-guide'], function () {
        Route::post('get-list', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'fetchTourGuide']);
        Route::post('details', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'fetchTourGuideDetails']);
    });

    //TODO @fa 2025-02-18 version V3 API
    Route::group(['prefix' => 'amusement-park'], function () {
        Route::post('get-list', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'fetchAmusementPark']);
        Route::post('details', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'fetchAmusementParkDetails']);
    });

    //TODO @fa 2025-02-18 version V3 API
    Route::group(['prefix' => 'haor'], function () {
        Route::post('get-list', [App\Http\Controllers\Api\V3\Haor\HaorController::class, 'fetchHaor']);
        Route::post('details', [App\Http\Controllers\Api\V3\Haor\HaorController::class, 'fetchHaorDetails']);
    });

    //TODO @fa 2025-02-18 version V3 API
    Route::group(['prefix' => 'tea-estate'], function () {
        Route::post('get-list', [App\Http\Controllers\Api\V3\TeaEstate\TeaEstateController::class, 'fetchTeaEstate']);
        Route::post('details', [App\Http\Controllers\Api\V3\TeaEstate\TeaEstateController::class, 'fetchTeaEstateDetails']);
    });


    //TODO @fa 2024-11-27 authentication
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('logout', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);
        Route::post('profile', [App\Http\Controllers\Api\V1\User\UserController::class, 'profile']);
        Route::post('profile-update', [App\Http\Controllers\Api\V1\User\UserController::class, 'profileUpdate']);
        Route::post('avatar-upload', [\App\Http\Controllers\Api\V1\User\UserController::class, 'avatarUpload']);
        //TODO @fa 2022-11-30 password update
        Route::post('password-update', [\App\Http\Controllers\Api\V1\User\UserController::class, 'passwordUpdate']);
        //TODO @fa 2024-12-04 user
        Route::group(['prefix' => 'user'], function () {
            Route::post('list', [App\Http\Controllers\Api\V1\User\UserController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V1\User\UserController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\User\UserController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\User\UserController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\User\UserController::class, 'delete']);
            Route::post('activity', [App\Http\Controllers\Api\V1\User\UserController::class, 'activity']);
        });
        //TODO @fa 11/24/24 guideline
        Route::group(['prefix' => 'guideline'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'storeGuideLine']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'editGuideLine']);
            Route::post('update', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'updateGuideLine']);
        });
        //TODO @fa 11/24/24 awareness
        Route::group(['prefix' => 'awareness'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'awarenessStore']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'editAwareness']);
            Route::post('update', [App\Http\Controllers\Api\V1\Tourist\TouristController::class, 'awarenessUpdate']);
        });
        //TODO @fa 2024-11-25 news
        Route::group(['prefix' => 'news'], function () {
            Route::post('list', [App\Http\Controllers\Api\V1\News\NewsController::class, 'list']);
            Route::post('store', [App\Http\Controllers\Api\V1\News\NewsController::class, 'store']);
            Route::post('update', [App\Http\Controllers\Api\V1\News\NewsController::class, 'update']);
            Route::post('edit', [App\Http\Controllers\Api\V1\News\NewsController::class, 'edit']);
            Route::post('publish', [App\Http\Controllers\Api\V1\News\NewsController::class, 'publish']);
            Route::post('delete', [App\Http\Controllers\Api\V1\News\NewsController::class, 'delete']);
        });
        //TODO @fa 2024-11-25 spot
        Route::group(['prefix' => 'spot'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'delete']);
            Route::post('dropdown', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'dropdown']);
            Route::post('delete-images', [App\Http\Controllers\Api\V1\Tourist\SpotController::class, 'deleteImages']);
        });
        //TODO @fa 2024-11-25 locker
        Route::group(['prefix' => 'locker'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'delete']);
            Route::post('dropdown', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'dropdown']);

            Route::post('introduction', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'introduction']);
            Route::post('introduction-store', [App\Http\Controllers\Api\V1\Locker\LockerController::class, 'introductionStore']);
        });
        //TODO @fa 2024-11-25 hotel
        Route::group(['prefix' => 'hotel'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelStore']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelEdit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelUpdate']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelDelete']);

            Route::post('type-dropdown', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'typeDropdown']);
            Route::post('category-dropdown', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'categoryDropdown']);

            //TODO @fa 2024-11-25 hotel type
            Route::post('type-store', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelTypeStore']);
            Route::post('type-edit', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelTypeEdit']);
            Route::post('type-update', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelTypeUpdate']);
            Route::post('type-delete', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelTypeDelete']);

            //TODO @fa 2024-11-25 category
            Route::post('category-store', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelCategoryStore']);
            Route::post('category-edit', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelCategoryEdit']);
            Route::post('category-update', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelCategoryUpdate']);
            Route::post('category-delete', [App\Http\Controllers\Api\V1\Hotel\HotelController::class, 'hotelCategoryDelete']);
        });

        //TODO @fa 2024-11-26 transport
        Route::group(['prefix' => 'transport'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Transport\TransportController::class, 'delete']);
        });

        //TODO @fa 2024-11-26 event
        Route::group(['prefix' => 'event'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Event\EventController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Event\EventController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Event\EventController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Event\EventController::class, 'destroy']);
        });

        //TODO @fa 2024-11-27 campaign
        Route::group(['prefix' => 'campaign'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'destroy']);
        });
        //TODO @fa 2024-11-28 cox bazaar
        Route::group(['prefix' => 'cox-bazaar'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'coxBazaarStore']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Campaign\CampaignController::class, 'coxBazaarEdit']);
        });

        //TODO @fa 2024-11-28 bank
        Route::group(['prefix' => 'bank'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankStore']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankEdit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankUpdate']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'bankDelete']);
        });
        //TODO @fa 2024-11-28 atm
        Route::group(['prefix' => 'atm'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'atmStore']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'atmEdit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'atmUpdate']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Bank\BankController::class, 'atmDelete']);
        });

        //TODO @fa 2024-11-28 red zone
        Route::group(['prefix' => 'red-zone'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'destroy']);
            //TODO @fa 2025-03-11 category added for v2
            Route::group(['prefix' => 'category'], function () {
                //Route::post('list',[App\Http\Controllers\Api\V1\RedZone\RedZoneController::class,'categoryList']);
                Route::post('store', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'categoryStore']);
                Route::post('edit', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'categoryEdit']);
                Route::post('update', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'categoryUpdate']);
                Route::post('delete', [App\Http\Controllers\Api\V1\RedZone\RedZoneController::class, 'categoryDelete']);
            });
        });

        //TODO @fa 2024-11-28 police
        Route::group(['prefix' => 'police'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Tourist\PoliceController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Tourist\PoliceController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Tourist\PoliceController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Tourist\PoliceController::class, 'delete']);
        });
        //TODO @fa 2024-11-28 doctor
        Route::group(['prefix' => 'doctor'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Doctor\DoctorController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Doctor\DoctorController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Doctor\DoctorController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Doctor\DoctorController::class, 'delete']);
        });
        //TODO @fa 2024-11-28 life guard
        Route::group(['prefix' => 'life-guard'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\LifeGuard\LifeGuardController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\LifeGuard\LifeGuardController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\LifeGuard\LifeGuardController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\LifeGuard\LifeGuardController::class, 'delete']);
        });
        //TODO @fa 2024-11-28 beach volunteer
        Route::group(['prefix' => 'beach-volunteer'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\BeachVolunteer\BeachVolunteerController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\BeachVolunteer\BeachVolunteerController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\BeachVolunteer\BeachVolunteerController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\BeachVolunteer\BeachVolunteerController::class, 'delete']);
        });
        //TODO @fa 2024-11-28 notice
        Route::group(['prefix' => 'notice'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Notice\NoticeController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Notice\NoticeController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V1\Notice\NoticeController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Notice\NoticeController::class, 'delete']);
        });
        //TODO @fa 2024-12-19 service
        Route::group(['prefix' => 'service'], function () {
            Route::post('store', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'store']);
            Route::post('get-service', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'type']);
            Route::post('update-service', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'updateService']);
            Route::post('edit', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'show']);
            Route::post('update', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V1\Service\ServiceController::class, 'destroy']);
        });


        //TODO @fa 2025-02-18 Version V2 API
        //TODO ***************************************************
        //TODO @fa 2025-02-18 restaurant
        Route::group(['prefix' => 'restaurant'], function () {
            //Route::post('list',[App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class,'index']);
            Route::post('store', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'destroy']);
            Route::group(['prefix' => 'category'], function () {
                Route::post('store', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'categoryStore']);
                Route::post('edit', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'categoryEdit']);
                Route::post('update', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'categoryUpdate']);
                Route::post('delete', [App\Http\Controllers\Api\V2\Restaurant\RestaurantController::class, 'categoryDestroy']);
            });
        });

        //TODO @fa 2025-02-20 fair board
        Route::group(['prefix' => 'fair-board'], function () {
            //Route::post('list',[App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class,'index']);
            Route::post('store', [App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class, 'store']);
            Route::post('edit', [App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class, 'edit']);
            Route::post('update', [App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V2\FairBoard\FairBoardController::class, 'destroy']);
        });
        //TODO @fa 2025-03-10 content
        Route::group(['prefix' => 'content'], function () {
            Route::post('list', [App\Http\Controllers\Api\V2\Content\ContentController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V2\Content\ContentController::class, 'update']);
            Route::post('edit', [App\Http\Controllers\Api\V2\Content\ContentController::class, 'edit']);
            Route::post('delete', [App\Http\Controllers\Api\V2\Content\ContentController::class, 'destroy']);
        });
        //TODO @fa 2025-03-11 tourist
        Route::group(['prefix' => 'tourist'], function () {
            Route::post('list', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'list']);
            Route::post('show', [App\Http\Controllers\Api\V2\Auth\AuthController::class, 'show']);
        });

        //TODO @fa 2025-03-11 complain
        Route::group(['prefix' => 'complain'], function () {
            Route::post('list', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'index']);
            Route::post('submit', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'complainStore']);
            Route::post('details', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'complainDetails']);
            Route::post('reply', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'reply']);
            Route::post('status-update', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'statusUpdate']);
            //TODO @fa 2025-03-11 category
            Route::group(['prefix' => 'category'], function () {
                Route::post('list', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'categoryList']);
                Route::post('store', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'store']);
                Route::post('edit', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'show']);
                Route::post('update', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'update']);
                Route::post('delete', [App\Http\Controllers\Api\V2\Complain\ComplainController::class, 'destroy']);
            });
        });

        //TODO @fa 2025-04-07 AI
        Route::group(['prefix' => 'ai'], function () {
            Route::post('tour-plan', [App\Http\Controllers\Api\V2\AI\TourPlanGenerateController::class, 'tourPlanGenerate'])->middleware('throttle:tour-plan');
            Route::post('list', [App\Http\Controllers\Api\V2\AI\TourPlanGenerateController::class, 'list']);
            Route::post('details', [App\Http\Controllers\Api\V2\AI\TourPlanGenerateController::class, 'details']);
        });


        // TODO @fa 2025-04-07 Tour Operator

        Route::group(['prefix' => 'tour-operator'], function () {
            Route::post('list', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'store']);
            Route::post('show', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'show']);
            Route::post('update', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'update']);
            Route::post('delete', [App\Http\Controllers\Api\V3\TourOperator\TourOperatorController::class, 'destroy']);
        });

        // TODO @fa 2025-04-07 Tour Guide
        Route::group(['prefix' => 'tour-guide'], function () {
            Route::post('list', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'store']);
            Route::post('show', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'show']);
            Route::post('update', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'update']);
            // Route::post('delete', [App\Http\Controllers\Api\V3\TourGuide\TourGuideController::class, 'destroy']);
        });


        // TODO @fa 2025-04-07 Amusement Park
        Route::group(['prefix' => 'amusement-park'], function () {
            Route::post('list', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'store']);
            Route::post('show', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'show']);
            Route::post('update', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'update']);
            // Route::post('delete', [App\Http\Controllers\Api\V3\AmusementPark\AmusementParkController::class, 'destroy']);
        });


        // TODO @fa 2025-04-07 Haor
        Route::group(['prefix' => 'haor'], function () {
            Route::post('list', [App\Http\Controllers\Api\V3\Haor\HaorController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V3\Haor\HaorController::class, 'store']);
            Route::post('show', [App\Http\Controllers\Api\V3\Haor\HaorController::class, 'show']);
            Route::post('update', [App\Http\Controllers\Api\V3\Haor\HaorController::class, 'update']);
        });

        // TODO @fa 2025-04-07 Tea Estate
        Route::group(['prefix' => 'tea-estate'], function () {
            Route::post('list', [App\Http\Controllers\Api\V3\TeaEstate\TeaEstateController::class, 'index']);
            Route::post('store', [App\Http\Controllers\Api\V3\TeaEstate\TeaEstateController::class, 'store']);
            Route::post('show', [App\Http\Controllers\Api\V3\TeaEstate\TeaEstateController::class, 'show']);
            Route::post('update', [App\Http\Controllers\Api\V3\TeaEstate\TeaEstateController::class, 'update']);
        });
    });
});
