<?php

namespace App\Http\Controllers\Api\V3\Rating;

use App\Http\Controllers\Controller;
use App\Models\RestaurantRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;

class RestaurantRatingController extends Controller
{
    // list of restaurant ratings
    public function index(Request $request)
    {
        try {
            $spotRattings = RestaurantRating::query()
                ->with(['restaurantParent.restaurants' => function ($query) use ($request) {
                    $query->select('id', 'name', 'restaurant_parent_id', 'language_id')->where('language_id', $request->input('language_id', 1));
                }, 'user'])
                ->when($request->filled('restaurant_parent_id'), function ($query) use ($request) {
                    $query->where('restaurant_parent_id', $request->restaurant_parent_id);
                })
                ->when($request->filled('user_id'), function ($query) use ($request) {
                    $query->where('user_id', $request->user_id);
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->paginate($request->integer('per_page', PaginationEnum::Max->value));

            return response()->json([
                'status_code' => 200,
                'data' => $spotRattings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'An error occurred while fetching restaurant ratings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // store restaurant rating
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant_parent_id' => 'required|exists:restaurants,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $data['user_id'] = Auth::id(); 

        try {
            $spotRatting = RestaurantRating::create($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant rating created successfully',
                'data' => $spotRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to create restaurant rating',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // change status of restaurant rating 
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
            'id' => 'required|exists:restaurant_ratings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        try {
            $spotRatting = RestaurantRating::findOrFail($data['id']);
            
            $spotRatting->update($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant rating status updated successfully',
                'data' => $spotRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update restaurant rating status',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
