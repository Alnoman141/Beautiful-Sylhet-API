<?php

namespace App\Http\Controllers\Api\V3\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiLimit;
use App\Models\District;
use App\Models\HotelCategory;
use App\Models\HotelType;
use App\Models\RestaurantCategory;
use App\Models\Spot;
use App\Models\TransportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
    // get dropdown data for AI
    public function getDropdown(Request $request) {
        $request->validate([
            'language_id' => 'required|exists:languages,id',
        ]);

        $languageId = $request->input('language_id');

        $dropdownData = [
            'hotel_types' => HotelType::where('language_id', $languageId)
                ->select('id', 'name')
                ->get(),
            'hotel_categories' => HotelCategory::where('language_id', $languageId)
                ->select('id', 'name')
                ->get(),
            'restaurant_categories' => RestaurantCategory::where('language_id', $languageId)
                ->select('id', 'name')
                ->get(),
            
            'transport_types' => TransportType::where('language_id', $languageId)
                ->select('id', 'name')
                ->get(),

            'spots' => Spot::where('language_id', $languageId)
                ->select('id', 'name')
                ->get(),

            'districts' => District::select('id', 'en', 'bn')
                ->get()
                ->map(function ($district) use ($languageId) {
                    return [
                        'id' => $district->id,
                        'name' => $languageId == 1 ? $district->bn : $district->en,
                    ];
                }),
        ];

        return response()->json([
            'status_code' => 200,
            'message' => 'Dropdown data fetched successfully',
            'data' => $dropdownData,
        ], 200);
    }

    // get ai limit for user
    public function index(Request $request) {
        $user = Auth::user();

        $aiLimit = AiLimit::where('user_id', $user->id)->first();

        return response()->json([
            'status_code' => 200,
            'message' => 'AI limit fetched successfully',
            'data' => $aiLimit,
        ], 200);
    }

    // set ai limit for user
    public function store(Request $request) {
        $user = Auth::user();

        $usersLimit = AiLimit::where('user_id', $user->id)->first();

        if (isset($usersLimit)) {
            $usersLimit->used = $usersLimit->used + 1;
            $usersLimit->save();
        } else {
            AiLimit::create([
                'user_id' => $user->id,
                'used' => 1,
            ]);
        }

        return response()->json([
            'status_code' => 200,
            'message' => 'AI limit updated successfully',
        ], 200);
    }
}
