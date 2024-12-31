<?php

namespace App\Http\Controllers\Api\V1\Event;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    //TODO @fa 2024-11-26 event list
    public function index(Request $request)
    {
        try {
            $events = Event::query()
                ->with(['parent','spot'=>fn($q)=>$q->where('language_id',$this->lang)])
                ->when($request->missing('web'),fn($a)=>$a->whereHas('parent',fn($a)=>$a->where('expiry','>',now()->format('Y-m-d H:i:s'))
                    ->orWhereNull('expiry')))
                ->where('language_id', $this->lang)
                ->latest()
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($event) {
                    return [
                        'id' => $event->id,
                        'parent_id' => $event->event_parent_id,
                        'name' => $event->name,
                        'date' => $event->date,
                        'time' => $event->time,
                        'location' => $event->location,
                        'description' => $event->description,
                        'terms' => $event->terms,
                        'fare' => $event->fare,
                        'phone' => $event->phone,
                        'image' => $event?->parent?->image,
                        'spot_id' => $event?->spot?->spot_parent_id,
                        'spot_name' => $event?->spot?->name,
                        'expiry' => $event?->parent?->expiry,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $events,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-26 event details
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:events,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $event = Event::query()
                ->where('language_id', $this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $event,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-26 event store
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string|max:255',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.location' => 'nullable|string|max:255',
            'data.*.time' => 'nullable|string|max:255',
            'data.*.phone' => 'nullable|string|max:255',
            'data.*.fare' => 'nullable|string|max:255',
            'data.*.description' => 'nullable|string',
            'data.*.terms' => 'nullable|string',
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validation->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = EventParent::create([
                'spot_parent_id' => $request->spot_parent_id,
                'image' => $request->hasFile('image') ? Fa::uploadImage($request->image, 'event') : null,
                'expiry' => $request->expiry,
            ]);
            foreach ($request->data as $data) {
                $parent->events()->create(collect($data)->only([
                    'name',
                    'language_id',
                    'location',
                    'time',
                    'phone',
                    'fare',
                    'description',
                    'terms',
                ])->toArray());
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Event created successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-26 event edit
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(),[
           'id'=>'required|integer|exists:event_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $data = EventParent::with(['events'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-26 event update
    public function update(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required|integer|exists:event_parents,id',
            'spot_parent_id' => 'nullable|integer|exists:spot_parents,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'data' => 'required|array',
            'data.*.name' => 'required|string|max:255',
            'data.*.id' => 'required|integer|exists:events,id',
            'data.*.language_id' => 'required|integer|exists:languages,id',
            'data.*.location' => 'nullable|string|max:255',
            'data.*.time' => 'nullable|string|max:255',
            'data.*.phone' => 'nullable|string|max:255',
            'data.*.fare' => 'nullable|string|max:255',
            'data.*.description' => 'nullable|string',
            'data.*.terms' => 'nullable|string',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validation->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = EventParent::query()
                ->findOrFail($request->id);
            $parent->update([
                'spot_parent_id' => $request->spot_parent_id ?? $parent->spot_parent_id,
                'expiry' => $request->expiry ?? $parent->expiry,
            ]);
            if ($request->hasFile('image')) {
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image' =>Fa::uploadImage($request->image, 'event'),
                ]);
            }
            foreach ($request->data as $data) {
                $parent->events()->updateOrCreate(
                    ['id' => $data['id']],
                    collect($data)->only([
                        'name',
                        'location',
                        'time',
                        'phone',
                        'fare',
                        'description',
                        'terms',
                    ])->toArray()
                );
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Event updated successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-26 event delete
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:event_parents,id',
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
            $event = EventParent::with('events')->findOrFail($request->id);
            Fa::deleteImage($event?->image);
            $event->events()->delete();
            $event->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Event deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
