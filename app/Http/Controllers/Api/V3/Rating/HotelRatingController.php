<?php

namespace App\Http\Controllers\Api\V3\Rating;

use App\Http\Controllers\Controller;
use App\Models\HotelRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;

class HotelRatingController extends Controller
{
    // list of hotel ratings
    public function index(Request $request)
    {
        try {
            $hotelRattings = HotelRating::query()
                ->with(['hotelParent.hotels' => function ($query) use ($request) {
                    $query->select('id', 'name', 'hotel_parent_id', 'language_id')->where('language_id', $request->input('language_id', 1));
                }, 'user'])
                ->when($request->filled('hotel_parent_id'), function ($query) use ($request) {
                    $query->where('hotel_parent_id', $request->hotel_parent_id);
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
                'data' => $hotelRattings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'An error occurred while fetching hotel ratings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // store hotel rating
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_parent_id' => 'required|exists:hotels,id',
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
            $hotelRatting = HotelRating::create($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel rating created successfully',
                'data' => $hotelRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to create hotel rating',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // change status of hotel rating 
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
            'id' => 'required|exists:hotel_ratings,id',
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
            $hotelRatting = HotelRating::findOrFail($data['id']);

            $data['status'] = $hotelRatting->status == 1 ? 0 : 1; // Toggle the status
            
            $hotelRatting->update($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel rating status updated successfully',
                'data' => $hotelRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update hotel rating status',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hotel_parent_id' => 'required|exists:hotels,id',
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
            $ratings = HotelRating::where('hotel_parent_id', $data['hotel_parent_id'])
                ->select('id', 'hotel_parent_id', 'user_id', 'rating', 'review', 'created_at')
                ->with(['user:id,first_name']) // Eager load user with selected fields
                ->paginate($request->integer('per_page', PaginationEnum::Max->value));

            return response()->json([
                'status_code' => 200,
                'data' => $ratings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'An error occurred while fetching hotel ratings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
