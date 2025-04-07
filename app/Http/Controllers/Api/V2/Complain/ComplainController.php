<?php

namespace App\Http\Controllers\Api\V2\Complain;

use App\Enum\ComplainStatusEnum;
use App\Enum\PaginationEnum;
use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\ComplainCategory;
use App\Models\ComplainCategoryParent;
use App\Models\ComplainReply;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ComplainController extends Controller
{
    //TODO @fa 2025-03-11 index
    public function index(Request $request)
    {
        try {
            $complains = Complain::query()
                ->withCount('replies')
                ->with(['category'=>fn($a)=>$a->where('language_id',$this->lang)->select('complain_categories.id','name','complain_category_parent_id')])
                ->when(auth()->user()->hasRole(RoleEnum::tourist->value), fn($q) => $q->where('user_id',auth()->id()))
                ->when(!auth()->user()->hasRole(RoleEnum::tourist->value), fn($q) => $q->with('user'))
                ->when($request->filled('category_id'), fn($q) => $q->where('complain_category_parent_id',$request->category_id))
                ->when($request->filled('serial'), fn($q) => $q->where('serial',$request->serial))
                ->latest()
                ->paginate($request->input('per_page', PaginationEnum::Default->value))
                ->through(function ($item){
                    $data = [
                        'id' => $item->id,
                        'category' => [
                            'id' => $item?->category?->complain_category_parent_id,
                            'name' => $item?->category?->name,
                        ],
                        'serial'=> $item->serial,
                        'description' => $item->description,
                        'image' => $item->image,
                        'file' => $item->file,
                        'date' => now()->parse($item->created_at)->format('Y-m-d h:i a'),
                        'status' => $item->status,
                        'replies_count' => $item->replies_count,
                    ];
                    if (!auth()->user()->hasRole(RoleEnum::tourist->value)){
                        $data['user'] = [
                            'id' => $item?->user?->id,
                            'name' => $item?->user?->name,
                            'avatar' => $item?->user?->avatar,
                            'phone' => $item?->user?->phone,
                            'email' => $item?->user?->email,
                        ];
                    }
                    return $data;
                });
            return response()->json([
                'status_code'=> 200,
                'data' => $complains
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 categoryList
    public function categoryList()
    {
        try {
            $complains = ComplainCategory::query()
                ->where('language_id',$this->lang)
                ->get()
                ->map(function ($item) {
                    return [
                        //'id' => $item->id,
                        'name' => $item->name,
                        'id' => $item->complain_category_parent_id,
                    ];
                });
            return response()->json([
                'status_code'=> 200,
                'data' => $complains
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=> 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 store
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = ComplainCategoryParent::create([]);
            foreach ($request->data as $data) {
                ComplainCategory::create([
                    'complain_category_parent_id' => $parent->id,
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Complain category created successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*id' => 'required|integer|exists:complain_categories,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                ComplainCategory::query()->where('id', $data['id'])->update([
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Complain category updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 show
    public function show(Request $request)
    {
        try {
            $complain = ComplainCategoryParent::query()
                ->with('categories')
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=>200,
                'data' => $complain
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 destroy
    public function destroy(Request $request)
    {
        try {
            $parent = ComplainCategoryParent::query()
                ->with('categories')
                ->findOrFail($request->id);
            $parent->categories()->delete();
            $parent->delete();
            return response()->json([
                'status_code'=>200,
                'message' => 'Complain category deleted successfully'
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 complain store
    public function complainStore(Request $request)
    {
        //abort_unless(Gate::allows(RoleEnum::tourist->value), 403);
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:complain_category_parents,id',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
            //max 20 mb
            'file'=>'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            Complain::create([
                'user_id'=>auth()->id(),
                'complain_category_parent_id' => $request->id,
                'description' => $request->description,
                'image' => $request->hasFile('image')? Fa::uploadImage($request->file('image'),'complain') : null,
                'file'=>$request->hasFile('file')? Fa::uploadFile($request->file('file'),'complain') : null,
                'serial'=>'V-'.now()->format('Y').Complain::count()+1
            ]);
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Complain created successfully'
            ], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-19 complainDetails
    public function complainDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:complains,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $complain = Complain::query()
                ->with(['category'=>fn($a)=>$a->where('language_id',$this->lang)->select('complain_categories.id','name'),'replies'=>['user:id,first_name,last_name,avatar']])
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=>200,
                'data' => $complain
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-19 reply
    public function reply(Request $request)
    {
        //abort_unless(Gate::allows(RoleEnum::tourist->value), 403);
        $validator = Validator::make($request->all(), [
            'complain_id' => 'required|integer|exists:complains,id',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'errors' => $validator->errors()
            ], 422);
        }
        $complain = Complain::query()->withCount('replies')->findOrFail($request->complain_id);
        if (!auth()->user()->hasRole(RoleEnum::tourist->value) && $complain->replies_count == 0){
            $complain->update([
                'status' => ComplainStatusEnum::in_progress->value,
            ]);
        }
        elseif (auth()->user()->hasRole(RoleEnum::tourist->value) && $complain->replies_count < 0){
            return response()->json([
                'status_code'=>403,
                'message' => 'You can not reply to this complain. Please wait for admin reply.',
            ], 403);
        }
        DB::beginTransaction();
        try {
            ComplainReply::create([
                'user_id'=>auth()->id(),
                'complain_id' => $request->complain_id,
                'description' => $request->description,
                'image' => $request->hasFile('image')? Fa::uploadFile($request->file('image'),'complain') : null,
            ]);
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Complain reply successfully',
            ], 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-19 status update
    public function statusUpdate(Request $request)
    {
       if (auth()->user()->hasRole(RoleEnum::tourist->value)) {
            return response()->json([
                'status_code' => 403,
                'message' => 'You are not allowed to access this action.',
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:complains,id',
            'status' => 'required|integer|not_in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $complain = Complain::query()->findOrFail($request->id);
            $complain->update([
                'status' => $request->status,
            ]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Complain status updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
}
