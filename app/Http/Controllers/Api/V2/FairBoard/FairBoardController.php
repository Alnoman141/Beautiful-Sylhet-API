<?php

namespace App\Http\Controllers\Api\V2\FairBoard;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\FairBoard;
use App\Models\FairBoardParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FairBoardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $fairs = FairBoard::query()
                ->with(['spot'=>fn($a)=>$a->where('language_id',$this->lang)->select('spots.id as id','spots.spot_parent_id','name')])
                ->where('language_id',$this->lang)
                ->when($request->filled('spot_parent_id'),fn($a)=>$a->whereHas('spot',fn($b)=>$b->where('spots.spot_parent_id',$request->spot_parent_id)))
                ->latest('id')
                ->take(1)
                //->paginate($request->per_page ?? PaginationEnum::Default->value)
                ->get()
                ->map(function ($item){
                    return [
                        'id' => $item->id,
                        'fair_board_parent_id' => $item->fair_board_parent_id,
                        'name' => $item->name,
                        'spot' => [
                            'id' => $item?->spot?->id,
                            'spot_parent_id' => $item?->spot?->spot_parent_id,
                            'name' => $item?->spot?->name,
                        ],
                    ];
                })->values()->first()
            ;
            return response()->json([
                'status_code' => 200,
                'data' => $fairs,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 22-Mar-25 show
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:fair_boards,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $data = FairBoard::query()->with(['spot'=>fn($a)=>$a
                ->where('language_id',$this->lang)
                ->select('spots.id as id','spots.spot_parent_id','name')])
                ->where('language_id',$this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-18 store fair board
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
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
            $parent = FairBoardParent::create([
                'spot_parent_id' => $request->spot_parent_id,
            ]);
            foreach ($request->data as $data) {
                $parent->fairBoards()->create([
                    'language_id' => $data['language_id'],
                    'name' => $data['name'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Fare board created successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-18 edit fair board
    public function edit(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'id'=>'required|integer|exists:fair_board_parents,id',
        ]);
        if ($validate->fails()){
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        try {
            $data = FairBoardParent::with('fairBoards')->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-18 update fair board
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:fair_board_parents,id',
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'nullable|integer|exists:fair_boards,id',
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
            if ($request->filled('spot_parent_id')){
                FairBoardParent::query()->where('id',$request->id)
                    ->update([
                    'spot_parent_id' => $request->spot_parent_id,
                ]);
            }
            foreach ($request->data as $data) {
                $board = FairBoard::findOrFail($data['id']);
                $board->update([
                    'language_id' => $data['language_id'],
                    'name' => $data['name'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Fare board updated successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-18 delete fair board
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:fair_board_parents,id',
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
            $board = FairBoardParent::findOrFail($request->id);
            $board->fairBoards()->delete();
            $board->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Fare board deleted successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
