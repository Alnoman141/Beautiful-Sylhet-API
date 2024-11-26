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
                if (array_key_exists('id', $data)) {
                    $introduction = LockerService::findOrFail($data['id']);
                }
                else {
                    $introduction = new LockerService();
                }
                $introduction->update([
                    'language_id' => $data['language_id'],
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
            'data' => 'required|array',
            'parent_spot_id' => 'required|integer|exists:spot_parents,id',
            'link' => 'nullable|string',
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
            if ($request->filled('images')){
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
            'id' => 'required|integer|exists:lockers,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $locker = Locker::findOrFail($request->id);
            $data = LockerParent::with(['images','lockers'])->findOrFail($locker->locker_parent_id);
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
            'id' => 'nullable|integer|exists:lockers,id',
            'spot_parent_id' => 'required|integer|exists:spot_parents,id',
            'data' => 'required|array',
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
            $locker = Locker::with('parent')->findOrFail($request->id);
            $locker->parent->update([
                'spot_parent_id' => $request->spot_parent_id,
                'link' => $request->link,
            ]);
            foreach ($request->data as $data) {
                $locker->update([
                    'language_id' => $data['language_id'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'description' => $data['description'],
                ]);
            }
            if ($request->filled('images')){
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
                ->where('language_id', $this->lang)
                //->latest()
                ->get();
            return response()->json([
                'status_code' => 200,
                'data' => $lockers,
                'message' => 'Locker list',
                'intro'=>$this->introduction(),
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
