<?php

namespace App\Http\Controllers\Api\V1\Tourist;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Spot;
use App\Models\SpotImage;
use App\Models\SpotParent;
use App\Services\Fa;
use App\Services\Weather;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SpotController extends Controller
{
    public function index(Request $request)
    {
        try {
            $spots = Spot::with(['parent' => fn($a) => $a->select('id', 'image')->with(['spot' => fn($q) => $q->select(['spot_parent_id', 'name'], ['ratings' => fn($r) => $r->select('rating')])->where('language_id', $this->lang == 1 ? 2 : 1)])])
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('id'), fn($q) => $q->where('id', $request->id))
                ->where('language_id', $this->lang)
                ->select('id', 'name', 'spot_parent_id', 'tag_line')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($spot) {
                    return [
                        'id' => $spot->id,
                        'parent_id' => $spot->spot_parent_id,
                        'name' => $spot->name,
                        'tag_line' => $spot->tag_line,
                        'name_en' => collect($spot?->parent?->spot)->pluck('name')->first(),
                        'image' => $spot?->parent?->image,
                        'ratings' => $spot?->parent->ratings?->count() ? $spot?->parent->ratings->avg('rating') : 0,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $spots
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 spot details
    public function details(Request $request)
    {
        try {
            $spot = Spot::with(['parent' => [
                'images',
                'hotels' => fn($q) => $q
                    ->where('language_id', $this->lang)
                    ->select(['hotels.id as id', 'hotel_parents.id as parent_id', 'name', 'phone'])
            ]])->where('language_id', $this->lang)->findOrFail($request->id);
            // 1. Eager load parent images + parent ratings (runs the queries)
            $spot->load([
                'parent.images',
                'parent.ratings' => fn($q) => $q
                    ->select('id', 'spot_parent_id', 'user_id', 'rating', 'review') // spot_id here = parent's id
                    ->with('user:id,first_name,last_name') // load user with only id, first_name, last_name, email
            ]);

            // 2. Reshape the parent's images (only if a parent exists)
            if ($spot->parent) {
                $spot->parent->setRelation(
                    'images',
                    $spot->parent->images->map(fn($image) => [
                        'link' => $image->name,
                    ])
                );
            }
            $map = $spot?->parent?->map_link;
            $link = Str::between($map, 'place/', '/@');
            $name = Str::lower(Str::replace('+', ' ', $link));
            return response()->json([
                'status_code' => 200,
                'data' => $spot,
                'weather' => Weather::openWeather($name, $spot?->spot_parent_id),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 create spot
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'link' => 'nullable',
            'map_link' => 'nullable',
            'data' => 'required|array',
            'data.*.name' => 'required',
            /*'data.*.description' => 'nullable',
            'data.*.tag_line' => 'nullable',
            'data.*.distance' => 'nullable',
            'data.*.travel_info' => 'nullable',
            'data.*.foods' => 'nullable',
            'data.*.visiting_time' => 'nullable',
            'data.*.rides' => 'nullable',
            'data.*.fair' => 'nullable',
            'data.*.details' => 'nullable',
            'data.*.safety_guideline' => 'nullable',
            'data.*.tourist_places' => 'nullable',
            'data.*.hazard_info' => 'nullable',
            'data.*.advice' => 'nullable',
            'data.*.others_info' => 'nullable',
            'data.*.risk_factor' => 'nullable',*/
            'data.*.language_id' => 'required|exists:languages,id',
            'images' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $spotParent = SpotParent::create([
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'spots') : null,
                'link' => $request->link,
                'map_link' => $request->map_link,
            ]);
            foreach ($request->data as $data) {
                $data['spot_parent_id'] = $spotParent->id;
                Spot::create($data);
            }
            if ($request->images && count($request->images) > 0) {
                foreach ($request->images as $image) {
                    $spotParent->images()->create([
                        'name' => Fa::uploadImage($image, 'spots-images'),
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Spot created successfully',
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
    //TODO @fa 11/24/24 edit spot
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:spot_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $data = SpotParent::with(['images', 'spot'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24  update spot
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:spot_parents,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'link' => 'nullable',
            'map_link' => 'nullable',
            'data' => 'required|array',
            'data.*.id' => 'required|exists:spots,id',
            'data.*.name' => 'required',
            /*'data.*.description' => 'nullable',
            'data.*.tag_line' => 'nullable',
            'data.*.distance' => 'nullable',
            'data.*.travel_info' => 'nullable',
            'data.*.foods' => 'nullable',
            'data.*.visiting_time' => 'nullable',
            'data.*.rides' => 'nullable',
            'data.*.fair' => 'nullable',
            'data.*.details' => 'nullable',
            'data.*.safety_guideline' => 'nullable',
            'data.*.tourist_places' => 'nullable',
            'data.*.hazard_info' => 'nullable',
            'data.*.advice' => 'nullable',
            'data.*.others_info' => 'nullable',
            'data.*.risk_factor' => 'nullable',*/
            'data.*.language_id' => 'required|exists:languages,id',
            'images' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $spotParent = SpotParent::findOrFail($request->id);
            $spotParent->update([
                'link' => $request->link,
                'map_link' => $request->map_link,
            ]);
            if ($request->hasFile('image')) {
                Fa::deleteImage($spotParent?->image);
                $spotParent->update([
                    'image' => Fa::uploadImage($request->file('image'), 'spots')
                ]);
            }
            //$spotParent->images()->delete();
            if ($request->images && count($request->images) > 0) {
                foreach ($request->images as $image) {
                    $spotParent->images()->create([
                        'name' => Fa::uploadImage($image, 'spots-images'),
                    ]);
                }
            }
            foreach ($request->data as $data) {
                $data['spot_parent_id'] = $spotParent->id;
                Spot::updateOrCreate(['id' => $data['id']], $data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Spot updated successfully',
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
    //TODO @fa 11/24/24 delete spot
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:spot_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $parent = SpotParent::with(['images', 'spot'])->findOrFail($request->id);
            if ($parent->images->isNotEmpty()) {
                foreach ($parent->images as $image) {
                    Fa::deleteImage($image?->name);
                    $image->delete();
                }
            }
            //$parent->spot()->delete();
            $parent?->spot->each->delete();
            Fa::deleteImage($parent?->image);
            $parent->delete();
            return response()->json([
                'status_code' => 200,
                'message' => 'Spot deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 tourist spot list dropdown
    public function dropdown()
    {
        try {
            $spots = SpotParent::query()
                ->withWhereHas('translate', fn($q) => $q->where('language_id', $this->lang))
                ->get()
                ->map(function ($spot) {
                    return [
                        'id' => $spot->id,
                        'name' => $spot?->translate?->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $spots
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-27 delete spot images as array ids
    public function deleteImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|exists:spot_images,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            foreach ($request->ids as $id) {
                $image = SpotImage::findOrFail($id);
                Fa::deleteImage($image?->name);
                $image->delete();
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Images deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
