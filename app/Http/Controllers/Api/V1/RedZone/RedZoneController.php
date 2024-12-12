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
                    ->select(['name','spots.spot_parent_id'])
                ])
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
                    ->select(['spots.id as id','name'])
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
            $parent->reds()->delete();
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
}
