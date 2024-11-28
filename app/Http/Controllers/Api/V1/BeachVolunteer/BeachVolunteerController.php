<?php

namespace App\Http\Controllers\Api\V1\BeachVolunteer;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\BeachVolunteer;
use App\Models\BeachVolunteerParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BeachVolunteerController extends Controller
{
    //TODO @fa 2024-11-28  List all beach volunteers
    public function index(Request $request)
    {
        try {
            $volunteers = BeachVolunteer::query()
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($volunteer) {
                    return [
                        'id' => $volunteer->id,
                        'parent_id' => $volunteer->beach_volunteer_parent_id,
                        'name' => $volunteer->name,
                        'phone' => $volunteer->phone,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $volunteers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    //TODO @fa 2024-11-28  Show specific beach volunteer
    public function show(Request $request)
    {
        try {
            $volunteer = BeachVolunteer::with(['spot' => fn($a) => $a
                ->where('language_id', $this->lang)
                ->select('spots.id as id', 'name')
            ])->where('language_id', $this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $volunteer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    //TODO @fa 2024-11-28  Store new beach volunteer
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
            $parent = BeachVolunteerParent::create([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                BeachVolunteer::create([
                    'beach_volunteer_parent_id' => $parent->id,
                    'language_id' => $data['language_id'],
                    'name' => $data['name'],
                    'phone' => @$data['phone'],
                    'instruction' => @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Beach volunteer created successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    //TODO @fa 2024-11-28  Edit beach volunteer
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:beach_volunteer_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $volunteer = BeachVolunteerParent::with('volunteer')
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $volunteer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    //TODO @fa 2024-11-28  Update beach volunteer
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:beach_volunteer_parents,id',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'data' => 'required|array',
            'data.*.id' => 'required|exists:beach_volunteers,id',
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
            $parent = BeachVolunteerParent::findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id
            ]);
            foreach ($request->data as $data) {
                $volunteer = BeachVolunteer::findOrFail($data['id']);
                $volunteer->update([
                    'language_id' => $data['language_id'],
                    'name' => $data['name'],
                    'phone' => @$data['phone'],
                    'instruction' => @$data['instruction'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Beach volunteer updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }

    //TODO @fa 2024-11-28  Delete beach volunteer
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:beach_volunteer_parents,id',
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
            $volunteer = BeachVolunteerParent::findOrFail($request->id);
            $volunteer->volunteer()->delete();
            $volunteer->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Beach volunteer deleted successfully'
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
