<?php

namespace App\Http\Controllers\Api\V3\Place;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\TopAttractivePlace;
use App\Models\TopAttractivePlaceParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Fa;

class TopAttractivePlaceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $places = TopAttractivePlace::with([
                'parent' => fn($a) => $a
                    ->select('id', 'image', 'spot_parent_id') // spot_parent_id needed if you load the spot relation
                    ->with([
                        // name in the other language (English <-> Bangla)
                        'topAttractivePlaces' => fn($q) => $q
                            ->select('top_attractive_place_parent_id', 'name')
                            ->where('language_id', $this->lang == 1 ? 2 : 1),
                    ]),
            ])
                ->when($request->filled('name'), fn($q) => $q->where('name', 'like', '%' . $request->name . '%'))
                ->when($request->filled('id'), fn($q) => $q->where('id', $request->id))
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->where('language_id', $this->lang)
                ->select('id', 'name', 'tag_line', 'tag', 'status', 'top_attractive_place_parent_id')
                ->paginate($request->integer('per_page', PaginationEnum::Max->value))
                ->through(function ($place) {
                    $parent = $place->parent;

                    return [
                        'id'             => $place->id,
                        'parent_id'      => $place->top_attractive_place_parent_id,
                        'name'           => $place->name,
                        'tag_line'       => $place->tag_line,
                        'tag'            => $place->tag,
                        'status'         => $place->status,
                        'image'          => $parent?->image,
                        'spot_parent_id' => $parent?->spot_parent_id,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'data' => $places
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:top_attractive_place_parents,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = TopAttractivePlaceParent::with('topAttractivePlaces')->findOrFail($request->id);

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.name' => 'required',
            'data.*.tag_line' => 'required',
            'data.*.tag' => 'required',
            'data.*.status' => 'nullable|in:0,1',
            'data.*.language_id' => 'required|exists:languages,id',
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
            $placeParent = TopAttractivePlaceParent::create([
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->file('image'), 'top-attractive-places') : null,
                'spot_parent_id' => $request->spot_parent_id,
            ]);

            foreach ($request->data as $data) {
                $data['top_attractive_place_parent_id'] = $placeParent->id;
                TopAttractivePlace::create($data);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Top attractive place created successfully',
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

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:top_attractive_place_parents,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|exists:top_attractive_places,id',
            'data.*.name' => 'required',
            'data.*.tag_line' => 'required',
            'data.*.tag' => 'required',
            'data.*.status' => 'nullable|in:0,1',
            'data.*.language_id' => 'required|exists:languages,id',
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
            $placeParent = TopAttractivePlaceParent::findOrFail($request->id);

            $placeParent->update([
                'spot_parent_id' => $request->spot_parent_id,
            ]);

            if ($request->hasFile('image')) {
                Fa::deleteImage($placeParent->image);
                $placeParent->update([
                    'image' => Fa::uploadImage($request->file('image'), 'top-attractive-places'),
                ]);
            }

            foreach ($request->data as $data) {
                $data['top_attractive_place_parent_id'] = $placeParent->id;
                TopAttractivePlace::updateOrCreate(['id' => $data['id']], $data);
            }

            DB::commit();

            return response()->json([
                'status_code' => 200,
                'message' => 'Top attractive place updated successfully',
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
}
