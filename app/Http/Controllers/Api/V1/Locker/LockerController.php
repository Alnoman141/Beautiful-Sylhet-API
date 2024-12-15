<?php

namespace App\Http\Controllers\Api\V1\Locker;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use App\Models\LockerParent;
use App\Models\LockerService;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LockerController extends Controller
{
    //TODO @fa 2024-11-25 locker introduction
    public function introduction()
    {
        try {
            $introduction = LockerService::query()
                ->where('language_id', $this->lang)
                ->latest()
                ->first();
            return response()->json([
                'status_code' => 200,
                'data' => $introduction
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 introduction store
    public function introductionStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.description' => 'required|string',
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
                LockerService::updateOrCreate([
                    'language_id' => $data['language_id'],
                ],[
                    'description' => $data['description'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Introduction created successfully',
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'required|integer|exists:spot_parents,id',
            'link' => 'nullable|string',
            'data' => 'required|array',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.phone' => 'nullable|max:20',
            'data.*.address' => 'nullable|string|max:255',
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
            $locker = LockerParent::create([
                'spot_parent_id' => $request->spot_parent_id,
                'link' => $request->link,
            ]);
            foreach ($request->data as $data) {
                $locker->lockers()->create([
                    'language_id' => $data['language_id'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'description' => $data['description'],
                ]);
            }
            if ($request->images && count($request->images) > 0) {
                foreach ($request->images as $image) {
                    $locker->images()->create([
                        'image' => Fa::uploadImage($image, 'locker'),
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Locker created successfully',
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
    //TODO @fa 2024-11-25 edit locker
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:locker_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $data = LockerParent::with(['images','lockers'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 update locker
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer|exists:locker_parents,id',
            'spot_parent_id' => 'required|integer|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:lockers,id',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'link' => 'nullable|string',
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
            $locker = LockerParent::findOrFail($request->id);
            $locker->update([
                'spot_parent_id' => $request->spot_parent_id,
                'link' => $request->link,
            ]);
            foreach ($request->data as $data) {
                Locker::findOrFail($data['id'])->update([
                    'language_id' => $data['language_id'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'description' => $data['description'],
                ]);
            }
            if ($request->images && count($request->images) > 0){
                foreach ($request->images as $image) {
                    $locker->images()->create([
                        'image' => Fa::uploadImage($image, 'locker'),
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Locker updated successfully',
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

    //TODO @fa 2024-11-25 list
    public function index()
    {
        try {
            $lockers = Locker::query()
                ->with(['parent','spot'=>fn($q) => $q->where('language_id', $this->lang)])
                ->where('language_id', $this->lang)
                //->latest()
                ->get()
                ->map(function ($locker){
                    return [
                        'id' => $locker->id,
                        'parent_id' => $locker->locker_parent_id,
                        'phone' => $locker->phone,
                        'address' => $locker->address,
                        'map_link' => $locker->parent?->link,
                        'description' => $locker->description,
                        'spot_id' => $locker?->spot?->id,
                        'spot_name' => $locker?->spot?->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'message' => 'Locker list',
                'data' => $lockers,
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 02-Dec-24 delete
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:locker_parents,id',
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
            $parent = LockerParent::with(['images'])->findOrFail($request->id);
            //TODO @fa 02-Dec-24 delete images
            if ($parent->images && count($parent->images) > 0) {
                foreach ($parent->images as $image) {
                    Fa::deleteImage($image->image);
                    $image->delete();
                }
            }
            $parent->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Locker deleted successfully',
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
}
