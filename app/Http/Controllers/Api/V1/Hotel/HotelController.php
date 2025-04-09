<?php

namespace App\Http\Controllers\Api\V1\Hotel;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\HotelCategoryParent;
use App\Models\HotelParent;
use App\Models\HotelType;
use App\Models\HotelTypeParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{
    //TODO @fa 2024-11-25 hotel type
    public function hotelTypeList()
    {
        try {
            $types = HotelType::query()
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
    //TODO @fa 2024-11-25 hotel type store
    public function hotelTypeStore(Request $request)
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
            $parent = HotelTypeParent::create([]);
            foreach ($request->data as $data) {
                $parent->types()->create($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel type created successfully',
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
    //TODO @fa 2024-11-25 edit hotel type
    public function hotelTypeEdit(Request $request)
    {
        try {
            $parent = HotelTypeParent::with(['types'])->findOrFail($request->id);
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
    //TODO @fa 2024-11-25 hotel type update
    public function hotelTypeUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:hotel_types,id',
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
                $type = HotelType::findOrFail($data['id']);
                $type->update($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel type updated successfully',
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
    //TODO @fa 2024-11-25 delete hotel type
    public function hotelTypeDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:hotel_type_parents,id',
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
            $type = HotelTypeParent::with('types')->findOrFail($request->id);
            $type->types()->delete();
            $type->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel type deleted successfully',
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

    //TODO @fa 2024-11-25 hotel category list
    public function hotelCategoryList()
    {
        try {
            $types = HotelCategory::query()
                ->with(['parent'=>fn($a)=>$a->with(['translate'=>fn($b)=>$b->where('language_id',$this->lang==1?2:1)])])
                ->where('language_id', $this->lang)
                ->get()
                ->map(function ($type){
                    $type['parent_id']=$type->hotel_category_parent_id;
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
    //TODO @fa 2024-11-25 hotel category store
    public function hotelCategoryStore(Request $request)
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
            $parent = HotelCategoryParent::create([]);
            foreach ($request->data as $data) {
                $parent->categories()->create($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel category created successfully',
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
    //TODO @fa 2024-11-25 edit hotel category
    public function hotelCategoryEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:hotel_category_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $parent = HotelCategoryParent::with(['categories'])->findOrFail($request->id);
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
    //TODO @fa 2024-11-25 hotel category update
    public function hotelCategoryUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:hotel_categories,id',
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
                $type = HotelCategory::findOrFail($data['id']);
                $type->update($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel category updated successfully',
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
    //TODO @fa 2024-11-25 delete hotel category
    public function hotelCategoryDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:hotel_category_parents,id',
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
            $type = HotelCategoryParent::with('categories')->findOrFail($request->id);
            $type->categories()->delete();
            $type->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel category deleted successfully',
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
    //TODO @fa 2024-11-25 Hotel list
    public function hotelList(Request $request)
    {
        try {
            $hotels = Hotel::query()
                ->with(['parent',
                    'spotParent'=>fn($a)=>$a->with(['translate'=>fn($b)=>$b->where('language_id',$this->lang)]),
                    'categoryParent'=>fn($a)=>$a->with(['translate'=>fn($b)=>$b->where('language_id',$this->lang)]),
                    'typeParent'=>fn($a)=>$a->with(['translate'=>fn($b)=>$b->where('language_id',$this->lang)]),
                ])
                ->when($request->filled('spot_parent_id'),fn($a)=>$a->whereHas('parent',fn($b)=>$b->where('spot_parent_id',$request->spot_parent_id)))
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($hotel){
                    return [
                        'id' => $hotel->id,
                        'parent_id' => $hotel->hotel_parent_id,
                        'name' => $hotel->name,
                        'phone' => $hotel->phone ?? '',
                        'address' => $hotel->address ?? '',
                        'description' => $hotel->description ?? '',
                        'website_link' => $hotel?->parent?->website_link ?? '',
                        'map_link' => $hotel?->parent?->map_link ?? '',
                        'image' => $hotel?->parent?->image ?? '',
                        'spot_id' => $hotel?->parent?->spot_parent_id ?? '',
                        'spot_name'=>$hotel?->spotParent?->translate?->name ?? '',
                        'category_name'=>$hotel?->categoryParent?->translate?->name ?? '',
                        'type_name'=>$hotel?->typeParent?->translate?->name ?? '',
                    ];
                })
            ;
            return response()->json([
                'status_code' => 200,
                'data' => $hotels
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 hotel store
    public function hotelStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'hotel_type_parent_id' => 'nullable|integer|exists:hotel_type_parents,id',
            'hotel_category_parent_id' => 'nullable|integer|exists:hotel_category_parents,id',
            'website_link' => 'nullable|string',
            'map_link' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.phone' => 'nullable|string',
            'data.*.address' => 'nullable|string',
            'data.*.description' => 'nullable|string',
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
            $parent = HotelParent::create([
                'spot_parent_id' => $request->spot_parent_id,
                'hotel_type_parent_id' => $request->hotel_type_parent_id,
                'hotel_category_parent_id' => $request->hotel_category_parent_id,
                'website_link' => $request->website_link,
                'map_link' => $request->map_link,
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->image,'hotel') : null,
            ]);
            foreach ($request->data as $data) {
                $parent->hotels()->create([
                    'name' => @$data['name'],
                    'language_id' => $data['language_id'],
                    'phone' => @$data['phone'] ?? null,
                    'address' => @$data['address'] ?? null,
                    'description' => @$data['description'] ?? null,
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel created successfully',
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
    //TODO @fa 2024-11-25 edit hotel
    public function hotelEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:hotel_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $parent = HotelParent::with(['hotels'])->findOrFail($request->id);
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
    //TODO @fa 2024-11-25 hotel update
    public function hotelUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:hotel_parents,id',
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'hotel_type_parent_id' => 'nullable|integer|exists:hotel_type_parents,id',
            'hotel_category_parent_id' => 'nullable|integer|exists:hotel_category_parents,id',
            'website_link' => 'nullable|string',
            'map_link' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:hotels,id',
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
            $parent = HotelParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id,
                'hotel_type_parent_id' => $request->hotel_type_parent_id,
                'hotel_category_parent_id' => $request->hotel_category_parent_id,
                'website_link' => $request->website_link,
                'map_link' => $request->map_link,
            ]);
            if ($request->hasFile('image')) {
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image' => Fa::uploadImage($request->image, 'hotel'),
                ]);
            }
            foreach ($request->data as $data) {
                $hotel = Hotel::findOrFail($data['id']);
                $hotel->update([
                    'name' => @$data['name'] ?? $hotel->name,
                    'language_id' => $data['language_id'] ?? $hotel->language_id,
                    'phone' => @$data['phone'] ?? $hotel->phone,
                    'address' => @$data['address'] ?? $hotel->address,
                    'description' => @$data['description'] ?? $hotel->description,
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 delete hotel
    public function hotelDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:hotel_parents,id',
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
            $hotel = HotelParent::with('hotels')->findOrFail($request->id);
            Fa::deleteImage($hotel?->image);
            //$hotel->hotels()->delete();
            $hotel?->hotels->each->delete();
            $hotel->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Hotel deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 typeDropdown
    public function typeDropdown()
    {
        try {
            $types = HotelTypeParent::query()
                ->withWhereHas('translate',fn($a)=>$a->where('language_id',$this->lang))
                ->get()
                ->map(fn($a)=>[
                    'id'=>$a->id,
                    'name'=>$a?->translate?->name,
                ]);
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
    //TODO @fa 2024-11-25 categoryDropdown
    public function categoryDropdown()
    {
        try {
            $types = HotelCategoryParent::query()
                ->withWhereHas('translate',fn($a)=>$a->where('language_id',$this->lang))
                ->get()
                ->map(fn($a)=>[
                    'id'=>$a->id,
                    'name'=>$a?->translate?->name,
                ]);
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
}
