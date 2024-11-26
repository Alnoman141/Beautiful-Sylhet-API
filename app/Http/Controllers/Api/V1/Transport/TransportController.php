<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Transport;
use App\Models\TransportParent;
use App\Models\TransportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransportController extends Controller
{
    //TODO @fa 2024-11-26 transport type list
    public function transportType()
    {
        try {
            $types = TransportType::query()
                ->where('language_id',$this->lang)
                ->get()
                ->map(function ($type) {
                    return [
                        'id' => $type->transport_type_parent_id,
                        'name' => $type->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $types,
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }

    //TODO @fa 2024-11-26 transport list
    public function index(Request $request)
    {
        try {
            $transports = Transport::query()
                ->when($request->transport_type_id,fn($query) => $query->where('transport_type_parent_id',$request->transport_type_id))
                ->where('language_id',$this->lang)
                ->paginate($request->integer('per_page',PaginationEnum::Default->value))
                ->through(function ($transport) {
                    return [
                        'id' => $transport->id,
                        'name' => $transport->name,
                        'time' => $transport->time,
                        'destination' => $transport->destination,
                        'phone' => $transport->phone,
                        'address' => $transport->address,
                        'fare' => $transport->fare,
                        'description' => $transport->description,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $transports,
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
    //TODO @fa 2024-11-26 transport details
    public function show(Request $request)
    {
        try {
            $transport = Transport::query()
                ->where('language_id',$this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $transport,
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
    //TODO @fa 2024-11-26 transport store
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'transport_type_parent_id' => 'required|exists:transport_type_parents,id',
            'map_link' => 'nullable|url|max:255',
            'data' => 'required|array',
            'data.*.name' => 'required|string|max:255',
            'data.*.time' => 'nullable|string|max:255',
            'data.*.destination' => 'nullable|string|max:255',
            'data.*.phone' => 'nullable|string|max:255',
            'data.*.address' => 'nullable|string|max:255',
            'data.*.fare' => 'nullable|string|max:255',
            'data.*.description' => 'nullable|string',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validation->errors(),
            ],422);
        }
        DB::beginTransaction();
        try {
            $parent = TransportParent::create([
                'transport_type_parent_id' => $request->transport_type_parent_id,
                'map_link' => $request->map_link,
            ]);
            foreach ($request->data as $data) {
                $parent->transports()->create($data);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Transport created successfully',
            ],200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
    //TODO @fa 2024-11-26 edit transport
    public function edit(Request $request)
    {
        try {
            $transport = Transport::query()
                ->findOrFail($request->id);
            $data = TransportParent::with('transports')
                ->findOrFail($transport->transport_parent_id);
            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
    //TODO @fa 2024-11-26 update transport
    public function update(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'id' => 'required|exists:transport_parents,id',
            'map_link' => 'nullable|url|max:255',
            'data' => 'required|array',
            'data.*.id' => 'required|exists:transports,id',
            'data.*.name' => 'required|string|max:255',
            'data.*.time' => 'nullable|string|max:255',
            'data.*.destination' => 'nullable|string|max:255',
            'data.*.phone' => 'nullable|string|max:255',
            'data.*.address' => 'nullable|string|max:255',
            'data.*.fare' => 'nullable|string|max:255',
            'data.*.description' => 'nullable|string',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validation->errors(),
            ],422);
        }
        DB::beginTransaction();
        try {
            $parent = TransportParent::query()
                ->findOrFail($request->id);
            $parent->update([
                'map_link' => $request->map_link,
            ]);
            foreach ($request->data as $data) {
                if (isset($data['id'])) {
                    $parent->transports()->findOrFail($data['id'])->update($data);
                }else {
                    $parent->transports()->update($data);
                }
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Transport updated successfully',
            ],200);
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
    //TODO @fa 2024-11-26 delete transport
    public function delete(Request $request)
    {
        try {
            $transport = Transport::with('parent')
                ->findOrFail($request->id);
            $transport->parent()->delete();
            $transport->delete();
            return response()->json([
                'status_code' => 200,
                'message' => 'Transport deleted successfully',
            ],200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
}
