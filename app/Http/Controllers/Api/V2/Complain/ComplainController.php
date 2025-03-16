<?php

namespace App\Http\Controllers\Api\V2\Complain;

use App\Enum\PaginationEnum;
use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\ComplainCategory;
use App\Models\ComplainCategoryParent;
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
                ->with(['category'=>fn($a)=>$a->where('language_id',$this->lang)->select('complain_categories.id','name')])
                ->latest()
                ->paginate($request->input('per_page', PaginationEnum::Default->value))
                ->through(function ($item){
                    return [
                        'id' => $item->id,
                        'category' => [
                            'id' => $item->category->id,
                            'name' => $item->category->name,
                        ],
                        'description' => $item->description,
                        'image' => $item->image,
                        'file' => $item->file,
                        'date' => now()->parse($item->created_at)->format('Y-m-d h:i a'),
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
                ComplainCategory::where('id', $data['id'])->update([
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
            $complain = ComplainCategory::query()
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
            'file'=>'nullable|mimes:mp4,mov,avi,wmv,flv|max:20480',
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
}
