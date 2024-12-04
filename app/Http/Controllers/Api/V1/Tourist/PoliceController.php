<?php

namespace App\Http\Controllers\Api\V1\Tourist;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Police;
use App\Models\PoliceParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Symfony\Component\Translation\t;

class PoliceController extends Controller
{
    //TODO @fa 2024-11-28 list of police
    public function index(Request $request)
    {
        try {
            $polices = Police::query()
                ->with(['spot'=>fn($a)=>$a
                    ->where('language_id',$this->lang)
                    ->select('spots.id as id','name','spots.spot_parent_id as spot_parent_id')
                ])
                ->when($request->filled('spot_parent_id'),fn($q) => $q->whereHas('parent',fn($q) => $q->where('spot_parent_id',$request->spot_parent_id)))
                ->where('language_id',$this->lang)
                ->get()
                ->paginate($request->integer('per_page',PaginationEnum::Default->value))
                ->through(function ($police){
                    return [
                        'id' => $police->id,
                        'parent_id'=> $police->police_parent_id,
                        'name' => $police->name,
                        'phone' => $police->phone,
                        'spot_id' => $police?->spot?->id,
                        'spot_parent_id' => $police?->spot?->spot_parent_id,
                        'spot_name' => $police?->spot?->name,
                    ];
                })
            ;
            return response()->json([
                'status_code'=> 200,
                'data' => $polices
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 show police
    public function show(Request $request)
    {
        try {
            $police = Police::with(['spot'=>fn($a)=>$a
                    ->where('language_id',$this->lang)
                    ->select('spots.id as id','name')
            ])->where('language_id',$this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=> 200,
                'data' => $police
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 store police
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
            $parent = PoliceParent::create([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                Police::create([
                    'police_parent_id'=> $parent->id,
                    'language_id'=> $data['language_id'],
                    'name'=> $data['name'],
                    'phone'=> @$data['phone'],
                    'instruction'=> @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=> 200,
                'message' => 'Tourist police created successfully'
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 edit police
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:police_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=> 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $police = PoliceParent::with('police')
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=> 200,
                'data' => $police
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 update police
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:police_parents,id',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data'=> 'required|array',
            'data.*.id' => 'required|exists:polices,id',
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
            $parent = PoliceParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                $police = Police::findOrFail($data['id']);
                $police->update([
                    'language_id'=> $data['language_id'],
                    'name'=> $data['name'],
                    'phone'=> @$data['phone'],
                    'instruction'=> @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=> 200,
                'message' => 'Tourist police updated successfully'
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 delete police
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> 'required|exists:police_parents,id',
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
            $police = PoliceParent::findOrFail($request->id);
            $police->police()->delete();
            $police->delete();
            DB::commit();
            return response()->json([
                'status_code'=> 200,
                'message' => 'Tourist police deleted successfully'
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
