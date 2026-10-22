<?php

namespace App\Http\Controllers\Api\V3\Rating;

use App\Http\Controllers\Controller;
use App\Models\TourOperatorRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;

class TourOperatorRatingController extends Controller
{
    // list of tour_operator ratings
    public function index(Request $request)
    {
        try {
            $ratings = TourOperatorRating::query()
                ->with([
                    'tour_operatorParent:id,logo',
                    'tour_operatorParent.operators' => function ($query) use ($request) {
                        $query->select('id', 'name', 'organization_name', 'tour_operator_parent_id', 'language_id')
                            ->where('language_id', $request->input('language_id', 1));
                    },
                    'user:id,first_name,last_name,avatar',
                ])
                ->when($request->filled('tour_operator_parent_id'), function ($query) use ($request) {
                    $query->where('tour_operator_parent_id', $request->tour_operator_parent_id);
                })
                ->when($request->filled('user_id'), function ($query) use ($request) {
                    $query->where('user_id', $request->user_id);
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($rating) {
                    $parent = $rating->tour_operatorParent;

                    return [
                        'id'         => $rating->id,
                        'rating'     => $rating->rating,
                        'review'     => $rating->review,
                        'status'     => (bool) $rating->status,
                        'created_at' => $rating->created_at,
                        'tour_operator' => $parent ? [
                            'id'   => $parent->id,
                            'name' => $parent->operators->first()?->name,
                            'organization_name' => $parent->operators->first()?->organization_name,
                            'logo' => $parent->logo,
                        ] : null,
                        'user' => $rating->user ? [
                            'id'     => $rating->user->id,
                            'name'   => $rating->user->name,
                            'avatar' => $rating->user->avatar,
                        ] : null,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'data'        => $ratings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message'     => 'An error occurred while fetching tour operator ratings.',
                'error'       => $e->getMessage(),
            ], 500);
        }
    }

    // store tour_operator rating
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_operator_parent_id' => 'required|exists:tour_operators,id',
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
            $tourOperatorRatting = TourOperatorRating::create($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operator rating created successfully',
                'data' => $tourOperatorRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to create tour_operator rating',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // change status of tour_operator rating 
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
            'id' => 'required|exists:tour_operator_ratings,id',
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
            $tourOperatorRatting = TourOperatorRating::findOrFail($data['id']);

            $tourOperatorRatting->update($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operator rating status updated successfully',
                'data' => $tourOperatorRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update tour operator rating status',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function getRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_operator_parent_id' => 'required|exists:tour_operators,id',
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
            $ratings = TourOperatorRating::where('tour_operator_parent_id', $data['tour_operator_parent_id'])
                ->select('id', 'tour_operator_parent_id', 'user_id', 'rating', 'review', 'created_at')
                ->with(['user:id,first_name']) // Eager load user with selected fields
                ->orderBy('created_at', 'desc')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value));

            return response()->json([
                'status_code' => 200,
                'message' => 'Tour operator ratings fetched successfully',
                'data' => $ratings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to fetch tour operator ratings',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
