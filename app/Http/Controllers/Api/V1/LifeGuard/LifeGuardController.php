<?php

namespace App\Http\Controllers\Api\V1\LifeGuard;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\LifeGuard;
use App\Models\LifeGuardParent;

class LifeGuardController extends Controller
{
    // TODO @fa 2024-11-28 list of life guards
    public function index(Request $request)
    {
        try {
            $lifeGuards = LifeGuard::query()
                ->with(['spot.translate'])
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($lifeGuard) {
                    return [
                        'id' => $lifeGuard->id,
                        'parent_id' => $lifeGuard->life_guard_parent_id,
                        'name' => $lifeGuard->name,
                        'phone' => $lifeGuard->phone,
                        'spot_id' => $lifeGuard?->spot?->id,
                        'spot_name' => $lifeGuard?->spot?->translate?->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $lifeGuards
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    // TODO @fa 2024-11-28 show life guard
    public function show(Request $request)
    {
        try {
            $lifeGuard = LifeGuard::with(['spot' => fn($a) => $a
                ->where('language_id', $this->lang)
                ->select('spots.id as id', 'name')
            ])->where('language_id', $this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $lifeGuard
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    // TODO @fa 2024-11-28 store life guard
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.name' => 'required|string|max:255',
            'data.*.language_id' => 'required|integer|not_in:0|exists:languages,id',
            'data.*.phone' => 'nullable|max:20',
            'data.*.instruction' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $parent = LifeGuardParent::create([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                LifeGuard::create([
                    'life_guard_parent_id' => $parent->id,
                    'language_id' => $data['language_id'],
                    'name' => $data['name'],
                    'phone' => @$data['phone'],
                    'instruction' => @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Life Guard created successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    // TODO @fa 2024-11-28 edit life guard
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:life_guard_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $lifeGuard = LifeGuardParent::with('guard')
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $lifeGuard
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    // TODO @fa 2024-11-28 update life guard
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:life_guard_parents,id',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|exists:life_guards,id',
            'data.*.name' => 'required|string|max:255',
            'data.*.language_id' => 'required|integer|not_in:0|exists:languages,id',
            'data.*.phone' => 'nullable|max:20',
            'data.*.instruction' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $parent = LifeGuardParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                $lifeGuard = LifeGuard::findOrFail($data['id']);
                $lifeGuard->update([
                    'language_id' => $data['language_id'],
                    'name' => $data['name'],
                    'phone' => @$data['phone'],
                    'instruction' => @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Life Guard updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    // TODO @fa 2024-11-28 delete life guard
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:life_guard_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $lifeGuard = LifeGuardParent::findOrFail($request->id);
            $lifeGuard->guard()->delete();
            $lifeGuard->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Life Guard deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
}
