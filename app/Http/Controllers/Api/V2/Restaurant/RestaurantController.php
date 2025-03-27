<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2025-02-17 12:40 p.m.
 */
namespace App\Http\Controllers\Api\V2\Restaurant;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use App\Models\RestaurantCategoryParent;
use App\Models\RestaurantParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        try {
            $restaurants = Restaurant::query()
                ->with(['parent','spot'=>fn($a)=>$a->where('language_id',$this->lang)->select('spots.spot_parent_id as id', 'name'),'category'=>fn($a)=>$a->where('language_id',$this->lang)->select('restaurant_categories.restaurant_category_parent_id as id', 'name')])
                ->where('language_id',$this->lang)
                ->when($request->filled('category_id'),fn($a)=>$a->whereHas('parent',fn($b)=>$b->where('restaurant_category_parent_id',$request->category_id)))
                ->when($request->filled('spot_parent_id'),fn($a)=>$a->whereHas('parent',fn($b)=>$b->where('spot_parent_id',$request->spot_parent_id)))
                ->when($request->filled('open_time'),fn($a)=>$a->where('open_time','>=',now()->parse($request->open_time)->format('H:i:s')))
                ->when($request->filled('close_time'),fn($a)=>$a->where('close_time','<=',$request->close_time))
                ->paginate($request->per_page ?? PaginationEnum::Default->value);
            return response()->json([
                'status_code' => 200,
                'data' => $restaurants,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 25-Mar-25 details
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurants,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $restaurant = Restaurant::query()
                ->with(['parent','spot'=>fn($a)=>$a->where('language_id',$this->lang)->select('spots.spot_parent_id as id', 'name'),'category'=>fn($a)=>$a->where('language_id',$this->lang)->select('restaurant_categories.restaurant_category_parent_id as id', 'name')])
                ->where('language_id',$this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $restaurant,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-17 store restaurant
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'restaurant_category_parent_id' => 'nullable|integer|exists:restaurant_category_parents,id',
            'map_link' => 'nullable|string',
            'website_link' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'phone' => 'nullable|string',
            'open_time' => 'nullable|date_format:H:i:s',
            'close_time' => 'nullable|date_format:H:i:s',
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.phone' => 'nullable|string',
            'data.*.address' => 'nullable|string',
            'data.*.description' => 'nullable|string',
            'data.*.menu' => 'nullable|string',
            'data.*.facilities' => 'nullable|string',
            'data.*.open_time' => 'nullable|date_format:H:i:s',
            'data.*.close_time' => 'nullable|date_format:H:i:s',
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
            $parent = RestaurantParent::create([
                'spot_parent_id' => $request->spot_parent_id,
                'restaurant_category_parent_id' => $request->restaurant_category_parent_id,
                'map_link' => $request->map_link,
                'website_link' => $request->website_link,
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->image,'restaurant') : null,
            ]);
            foreach ($request->data as $data){
                Restaurant::create([
                    'restaurant_parent_id' => $parent->id,
                    'language_id' => $data['language_id'],
                    'name' => @$data['name'] ?? null,
                    'phone' => @$data['phone'] ?? $request->phone,
                    'address' => @$data['address'] ?? null,
                    'description' => @$data['description'] ?? null,
                    'menu' => @$data['menu'] ?? null,
                    'facilities' => @$data['facilities'] ?? null,
                    'open_time' => @$data['open_time'] ?? $request->open_time,
                    'close_time' => @$data['close_time'] ?? $request->close_time,
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant created successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-17 update restaurant
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'restaurant_category_parent_id' => 'nullable|integer|exists:restaurant_category_parents,id',
            'map_link' => 'nullable|string',
            'website_link' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'phone' => 'nullable|string',
            'open_time' => 'nullable|date_format:H:i:s',
            'close_time' => 'nullable|date_format:H:i:s',
            'id' => 'required|integer|exists:restaurant_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:restaurants,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.phone' => 'nullable|string',
            'data.*.address' => 'nullable|string',
            'data.*.description' => 'nullable|string',
            'data.*.menu' => 'nullable|string',
            'data.*.facilities' => 'nullable|string',
            'data.*.open_time' => 'nullable|date_format:H:i:s',
            'data.*.close_time' => 'nullable|date_format:H:i:s',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $parent = RestaurantParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id,
                'website_link' => $request->website_link,
                'map_link' => $request->map_link,
                'restaurant_category_parent_id' => $request->restaurant_category_parent_id,
            ]);
            if ($request->hasFile('image')) {
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image' => Fa::uploadImage($request->image, 'restaurant'),
                ]);
            }
            foreach ($request->data as $data){
                $restaurant = Restaurant::findOrFail($data['id']);
                $restaurant->update([
                    'language_id' => $data['language_id'],
                    'name' => @$data['name'] ?? $restaurant->name,
                    'phone' => @$data['phone'] ?? $request->phone,
                    'address' => @$data['address'] ?? null,
                    'description' => @$data['description'] ?? null,
                    'menu' => @$data['menu'] ?? null,
                    'facilities' => @$data['facilities'] ?? null,
                    'open_time' => @$data['open_time'] ?? $request->open_time,
                    'close_time' => @$data['close_time'] ?? $request->close_time,
                ]);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant updated successfully',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-17 show restaurant
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurant_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $restaurant = RestaurantParent::with('restaurants')->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $restaurant,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-18 delete restaurant
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurant_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $restaurant = RestaurantParent::with('restaurants')->findOrFail($request->id);
            Fa::deleteImage($restaurant->image);
            $restaurant->restaurants()->delete();
            $restaurant->delete();
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant deleted successfully',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-03-23 restaurant list
    public function categoryList()
    {
        try {
            $restaurants = RestaurantCategory::query()
                ->where('language_id',$this->lang)
                ->get()
                ->map(fn($a) => [
                    //'id' => $a->id,
                    'name' => $a->name,
                    'language_id' => $a->language_id,
                    'id' => $a->restaurant_category_parent_id
                ]);
            return response()->json([
                'status_code' => 200,
                'data' => $restaurants,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-03-23 category store
    public function categoryStore(Request $request)
    {
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
            $parent = RestaurantCategoryParent::create();
            foreach ($request->data as $data){
                RestaurantCategory::create([
                    'restaurant_category_parent_id' => $parent->id,
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant category created successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-03-23 category update
    public function categoryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'id' => 'required|integer|exists:restaurant_category_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:restaurant_categories,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            //$parent = RestaurantCategoryParent::findOrFail($request->id);
            foreach ($request->data as $data){
                $category = RestaurantCategory::findOrFail($data['id']);
                $category->update([
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant category updated successfully',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-03-23 category show
    public function categoryEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurant_category_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $category = RestaurantCategoryParent::with('categories')->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $category,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-03-23 category delete
    public function categoryDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurant_category_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $category = RestaurantCategoryParent::with('categories')->findOrFail($request->id);
            $category->categories()->delete();
            $category->delete();
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant category deleted successfully',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
