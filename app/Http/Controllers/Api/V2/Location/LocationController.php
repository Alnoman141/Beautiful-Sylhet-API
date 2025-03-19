<?php

namespace App\Http\Controllers\Api\V2\Location;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    //TODO @fa 2025-03-18 division
    public function division()
    {
        try {
            $divisions = Division::query()
                ->get()
                ->map(function ($item){
                    return [
                        'id' => $item->id,
                        'name'=>$this->lang==1?$item->bn:$item->en,
                    ];
                });
            return response()->json([
                'status_code'=> 200,
                'data' => $divisions
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-18 district
    public function district(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division_id' => 'nullable|integer|exists:divisions,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $districts = District::query()
                ->when($request->filled('division_id'),fn($q) => $q->where('division_id',$request->division_id))
                ->get()
                ->map(function ($item){
                    return [
                        'id' => $item->id,
                        'name'=>$this->lang==1?$item->bn:$item->en,
                    ];
                });
            return response()->json([
                'status_code'=> 200,
                'data' => $districts
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-18 upazila
    public function upazila(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required|integer|exists:districts,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $upazilas = Upazila::query()
                ->where('district_id',$request->district_id)
                ->get()
                ->map(function ($item){
                    return [
                        'id' => $item->id,
                        'name'=>$this->lang==1?$item->bn:$item->en,
                    ];
                });
            return response()->json([
                'status_code'=> 200,
                'data' => $upazilas
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
}
