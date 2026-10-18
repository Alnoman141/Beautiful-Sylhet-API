<?php

namespace App\Http\Controllers\Api\V3\Rating;

use App\Http\Controllers\Controller;
use App\Models\SpotRatting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enum\PaginationEnum;
use Illuminate\Support\Facades\Auth;

class SpotRattingController extends Controller
{

    // list of spot ratting
    public function index(Request $request)
    {
        $spotRattings = SpotRatting::query()
            ->with(['spotParent.spot' => function ($query) use ($request) {
                $query->select('id', 'name', 'spot_parent_id', 'language_id')->where('language_id', $request->input('language_id', 1));
            }, 'user'])
            ->when($request->filled('spot_parent_id'), function ($query) use ($request) {
                $query->where('spot_parent_id', $request->spot_parent_id);
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
    }

    // store spot ratting
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'required|exists:spot_parents,id',
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

        $data['user_id'] = Auth::id(); // Add the authenticated user's ID to the data

        try {
            $spotRatting = SpotRatting::create($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Spot ratting created successfully',
                'data' => $spotRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to create spot ratting',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // update spot ratting
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
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

        try {
            $spotRatting = SpotRatting::findOrFail($id);
            $spotRatting->update($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Spot ratting updated successfully',
                'data' => $spotRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update spot ratting',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    // change status of spot ratting 
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
            'id' => 'required|exists:spot_rattings,id',
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
            $spotRatting = SpotRatting::findOrFail($data['id']);
            
            $spotRatting->update($data);

            return response()->json([
                'status_code' => 200,
                'message' => 'Spot ratting status updated successfully',
                'data' => $spotRatting,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to update spot ratting status',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
