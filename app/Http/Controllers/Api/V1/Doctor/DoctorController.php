<?php

namespace App\Http\Controllers\Api\V1\Doctor;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    //TODO @fa 2024-11-28 list of doctors
    public function index(Request $request)
    {
        try {
            $doctors = Doctor::query()
                ->with(['spot'=>fn($a)=>$a
                    ->where('language_id',$this->lang)
                    ->select('spots.id as id','name','spots.spot_parent_id as spot_parent_id')
                ])
                ->where('language_id',$this->lang)
                ->paginate($request->integer('per_page',PaginationEnum::Default->value))
                ->through(function ($police){
                    return [
                        'id' => $police->id,
                        'parent_id'=> $police->doctor_parent_id,
                        'name' => $police->name,
                        'phone' => $police->phone,
                        'spot_id' => $police?->spot?->id,
                        'spot_parent_id' => $police?->spot?->spot_parent_id,
                        'spot_name' => $police?->spot?->name,
                    ];
                });
            return response()->json([
                'status_code'=> 200,
                'data' => $doctors
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 show doctor
    public function show(Request $request)
    {
        try {
            $doctor = Doctor::with(['spot'=>fn($a)=>$a
                ->where('language_id',$this->lang)
                ->select('spots.id as id','name')
            ])->where('language_id',$this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=> 200,
                'data' => $doctor
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 store doctor
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data'=> 'required|array',
            'data.*.name' => 'required|string|max:255',
            'data.*.language_id' => 'required|integer|not_in:0|exists:languages,id',
            'data.*.phone' => 'nullable|max:20',
            'data.*.instruction' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=> 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $parent = DoctorParent::create([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                Doctor::create([
                    'doctor_parent_id'=> $parent->id,
                    'language_id'=> $data['language_id'],
                    'name'=> $data['name'],
                    'phone'=> @$data['phone'],
                    'instruction'=> @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=> 200,
                'message' => 'Doctor created successfully'
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 edit doctor
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:doctor_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=> 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $doctor = DoctorParent::with('doctor')
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=> 200,
                'data' => $doctor
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 update doctor
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:doctor_parents,id',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data'=> 'required|array',
            'data.*.id' => 'required|exists:doctors,id',
            'data.*.name' => 'required|string|max:255',
            'data.*.language_id' => 'required|integer|not_in:0|exists:languages,id',
            'data.*.phone' => 'nullable|max:20',
            'data.*.instruction' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=> 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $parent = DoctorParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id ?? $parent->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                $doctor = Doctor::findOrFail($data['id']);
                $doctor->update([
                    'language_id'=> $data['language_id'],
                    'name'=> $data['name'],
                    'phone'=> @$data['phone'],
                    'instruction'=> @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=> 200,
                'message' => 'Doctor updated successfully'
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 delete doctor
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:doctor_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=> 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $doctor = DoctorParent::findOrFail($request->id);
            $doctor->doctor()->delete();
            $doctor->delete();
            DB::commit();
            return response()->json([
                'status_code'=> 200,
                'message' => 'Doctor deleted successfully'
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
}
