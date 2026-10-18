<?php

namespace App\Http\Controllers\Api\V3\SpotType;

use App\Http\Controllers\Controller;
use App\Models\SpotType;
use App\Models\SpotTypeParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SpotTypeController extends Controller
{
    // index
    public function index(Request $request)
    {
        // get all spot types
        try {
            $types = SpotType::query()
                ->with(['parent'=>fn($a)=>$a->with(['translate'=>fn($b)=>$b->where('language_id',$this->lang==1?2:1)])])
                ->where('language_id', $this->lang)
                ->get()
                ->map(function ($type){
                    $type['parent_id']=$type->hotel_type_parent_id;
                    $type['name_en']=$type?->parent?->translate?->name;
                    unset($type['parent']);
                    return $type;
                });
            return response()->json([
                'status_code' => 200,
                'data' => $types
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    // store
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = SpotTypeParent::create([]);
            foreach ($request->data as $data) {
                $parent->types()->create($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Spot type created successfully',
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

    // show
    public function show(Request $request) {
        try {
            $parent = SpotTypeParent::with(['types'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $parent
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    // update
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:spot_types,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                $type = SpotType::findOrFail($data['id']);
                $type->update($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Spot type updated successfully',
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
