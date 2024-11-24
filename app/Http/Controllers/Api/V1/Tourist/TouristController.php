<?php

namespace App\Http\Controllers\Api\V1\Tourist;

use App\Http\Controllers\Controller;
use App\Models\TouristAwareness;
use App\Models\TouristGuideline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TouristController extends Controller
{
    //TODO @fa 11/24/24 guideLineList
    public function guideLineList(Request $request)
    {
        try {
            $guideLines = TouristGuideline::query()
                ->where('language_id', $this->lang)
                ->select('id', 'name')
                ->get();
            return response()->json([
                'status_code' => 200,
                'data' => $guideLines
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 guideLineDetails
    public function guideLineDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tourist_guidelines,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $guideLine = TouristGuideline::query()
                ->where('id', $request->id)
                ->where('language_id', $this->lang)
                ->select('id', 'name')
                ->first();
            return response()->json([
                'status_code' => 200,
                'data' => $guideLine
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 storeGuideLine
    public function storeGuideLine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                TouristGuideline::query()->create([
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'GuideLine created successfully',
            ], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 updateGuideLine
    public function updateGuideLine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.id' => 'required|exists:tourist_guidelines,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                TouristGuideline::query()
                    ->where('id', $data['id'])
                    ->update([
                        'name' => $data['name'],
                    ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'GuideLine updated successfully',
            ], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    //TODO @fa 11/24/24 Tourist Awareness
    public function awarenessList(Request $request)
    {
        try {
            $awareness = TouristGuideline::query()
                ->where('language_id', $this->lang)
                ->select('id', 'name')
                ->get();
            return response()->json([
                'status_code' => 200,
                'data' => $awareness
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 touristAwarenessDetails
    public function awarenessDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tourist_guidelines,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $awareness = TouristAwareness::query()
                ->where('id', $request->id)
                ->where('language_id', $this->lang)
                ->select('id', 'name')
                ->first();
            return response()->json([
                'status_code' => 200,
                'data' => $awareness
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 touristAwarenessStore
    public function awarenessStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                TouristAwareness::query()->create([
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Tourist Awareness created successfully',
            ], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 touristAwarenessUpdate
    public function awarenessUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.id' => 'required|exists:tourist_awarenesses,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                TouristAwareness::query()
                    ->where('id', $data['id'])
                    ->update([
                        'name' => $data['name'],
                    ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Tourist Awareness updated successfully',
            ], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

}
