<?php

namespace App\Http\Controllers\Api\V3\Rating;

use App\Http\Controllers\Controller;
use App\Models\TourGuideRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;

class TourGuideRatingController extends Controller
{
    // list of tour_guide ratings
    public function index(Request $request)
    {
        try {
            $tourGuideRattings = TourGuideRating::query()
                ->with(['tour_guideParent.tour_guides' => function ($query) use ($request) {
                    $query->select('id', 'name', 'tour_guide_parent_id', 'language_id')->where('language_id', $request->input('language_id', 1));
                }, 'user'])
                ->when($request->filled('tour_guide_parent_id'), function ($query) use ($request) {
                    $query->where('tour_guide_parent_id', $request->tour_guide_parent_id);
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
                'data' => $tourGuideRattings
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'An error occurred while fetching tour_guide ratings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // store tour_guide rating
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_guide_parent_id' => 'required|exists:tour_guides,id',
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
            $tourGuideRatting = TourGuideRating::create($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour guide rating created successfully',
                'data' => $tourGuideRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to create tour_guide rating',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // change status of tour_guide rating 
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
            'id' => 'required|exists:tour_guide_ratings,id',
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
            $tourGuideRatting = TourGuideRating::findOrFail($data['id']);
            
            $tourGuideRatting->update($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour guide rating status updated successfully',
                'data' => $tourGuideRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update tour guide rating status',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_guide_parent_id' => 'required|exists:tour_guides,id',
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
            $ratings = TourGuideRating::where('tour_guide_parent_id', $data['tour_guide_parent_id'])
            ->select('id', 'tour_guide_parent_id', 'user_id', 'rating', 'review', 'created_at')
            ->with(['user:id,first_name']) // Eager load the user relationship with selected fields
            ->paginate($request->integer('per_page', PaginationEnum::Max->value));

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour guide ratings fetched successfully',
                'data' => $ratings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to fetch tour guide ratings',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
