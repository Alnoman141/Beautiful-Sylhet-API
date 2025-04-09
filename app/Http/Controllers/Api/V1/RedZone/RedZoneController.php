<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-11-28 5:17 p.m.
 */
namespace App\Http\Controllers\Api\V1\RedZone;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\RedZone;
use App\Models\RedZoneCategory;
use App\Models\RedZoneCategoryParent;
use App\Models\RedZoneParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RedZoneController extends Controller
{
    //TODO @fa 2024-11-28 list of red zones
    public function index(Request $request)
    {
        try {
            $reds = RedZone::query()
                ->with(['parent','spot'=>fn($q) => $q
                    ->where('language_id',$this->lang)
                    ->select(['name','spots.spot_parent_id']),
                    'category'=>fn($q) => $q
                        ->where('language_id',$this->lang)
                        ->select(['name','red_zone_categories.red_zone_category_parent_id'])
                ])
                ->when($request->filled('category_id'),fn($a)=>$a->whereHas('parent',fn($b)=>$b->where('red_zone_category_parent_id',$request->category_id)))
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($red) {
                    return [
                        'id' => $red->id,
                        'parent_id' => $red->red_zone_parent_id,
                        'name' => $red->name,
                        'map_link' => $red?->parent?->map_link,
                        'image' => $red?->parent?->image,
                        'spot_id' => $red?->spot?->spot_parent_id,
                        'spot_name' => $red?->spot?->name,
                        'instruction' => $red->instruction,
                        'category_id' => $red->category?->red_zone_category_parent_id,
                        'category_name' => $red->category?->name,
                    ];
                })
            ;
            return response()->json([
                'status_code'=>200,
                'data' => $reds
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 detail of red zone
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:red_zones,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $red = RedZone::with(['parent','spot'=>fn($q) => $q
                    ->where('language_id',$this->lang)
                    ->select(['spots.id as id','name']),
                'category'=>fn($q) => $q
                    ->where('language_id',$this->lang)
                    ->select(['red_zone_categories.id as id','name'])
                ])->where('language_id', $this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code'=>200,
                'data' => $red
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 store red zone
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'red_zone_category_parent_id' => 'nullable|integer|exists:red_zone_category_parents,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
            'map_link' => 'nullable|string',
            'short_description' => 'nullable|string',
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.instruction' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = RedZoneParent::create([
                'spot_parent_id' => $request->spot_parent_id,
                'red_zone_category_parent_id' => $request->red_zone_category_parent_id,
                'image'=>$request->hasFile('image') ? Fa::uploadImage($request->image, 'red-zones') : null,
                'map_link' => $request->map_link,
                'short_description' => $request->short_description,
            ]);
            foreach ($request->data as $data) {
                RedZone::create([
                    'red_zone_parent_id' => $parent->id,
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                    'instruction' => @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Red zone created successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 update red zone
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:red_zone_parents,id',
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'red_zone_category_parent_id' => 'nullable|integer|exists:red_zone_category_parents,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:red_zones,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.instruction' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = RedZoneParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id ?? $parent->spot_parent_id,
                'red_zone_category_parent_id' => $request->red_zone_category_parent_id ?? $parent->red_zone_category_parent_id,
                'map_link' => $request->map_link ?? $parent->map_link,
                'short_description' => $request->short_description ?? $parent->short_description,
            ]);
            if ($request->hasFile('image')) {
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image' => Fa::uploadImage($request->image, 'red-zones')
                ]);
            }
            foreach ($request->data as $data) {
                $red = RedZone::findOrFail($data['id']);
                $red->update([
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                    'instruction' => @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Red zone updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 edit red zone
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:red_zone_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $red = RedZoneParent::with('reds')->findOrFail($request->id);
            return response()->json([
                'status_code'=>200,
                'data' => $red
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 delete red zone
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:red_zone_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $parent = RedZoneParent::with('reds')->findOrFail($request->id);
            Fa::deleteImage($parent?->image);
            //$parent->reds()->delete();
            collect($parent?->reds)->each(function ($item){
                $item->delete();
            });
            $parent->delete();
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Red zone deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 category of red zone
    public function categoryList(Request $request)
    {
        try {
            $category = RedZoneCategory::query()
                ->where('language_id', $this->lang)
                //->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->get()
                //->through(function ($category) {
                ->map(function ($category) {
                    return [
                        //'id' => $category->id,
                        'name' => $category->name,
                        'id' => $category->red_zone_category_parent_id,
                    ];
                })
            ;
            return response()->json([
                'status_code'=>200,
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 store red zone category parent
    public function categoryStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'name' => 'required|string',
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = RedZoneCategoryParent::create([]);
            foreach ($request->data as $data) {
                RedZoneCategory::create([
                    'red_zone_category_parent_id' => $parent->id,
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Red zone category created successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 update red zone category parent
    public function categoryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'id' => 'required|integer|exists:red_zone_category_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:red_zone_categories,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            //$parent = RedZoneCategoryParent::findOrFail($request->id);
            foreach ($request->data as $data) {
                $category = RedZoneCategory::findOrFail($data['id']);
                $category->update([
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Red zone category updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 detail of red zone category
    public function categoryEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:red_zone_category_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $category = RedZoneCategoryParent::with('category')->findOrFail($request->id);
            return response()->json([
                'status_code'=>200,
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2025-03-11 delete red zone category parent
    public function categoryDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:red_zone_category_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $parent = RedZoneCategoryParent::with('category')->findOrFail($request->id);
            $parent->category()->delete();
            $parent->delete();
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message' => 'Red zone category deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
}
