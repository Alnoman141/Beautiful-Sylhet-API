<?php

namespace App\Http\Controllers\Api\V1\Notice;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
{
    //TODO @fa 2024-11-28 notice list
    public function index(Request $request)
    {
        try {
            $notices = Notice::query()
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($notice) {
                    return [
                        'id' => $notice->id,
                        'parent_id' => $notice->notice_parent_id,
                        'name' => $notice->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $notices
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 show notice
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:notices,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $notice = Notice::with(['parent'])
                ->where('language_id', $this->lang)
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $notice
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 store notice
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'=>'nullable|image|mimes:jpeg,png,jpg',
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = NoticeParent::create([
                'image'=>$request->hasFile('image') ? Fa::uploadImage($request->file('image'),'notice') : null
            ]);
            foreach ($request->data as $data) {
                Notice::create([
                    'notice_parent_id' => $parent->id,
                    'name' => $data['name'],
                    'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Notice created successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 edit notice
    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:notice_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
        try {
            $data = NoticeParent::with(['notice'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 update notice
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:notice_parents,id',
            'image'=>'nullable|image|mimes:jpeg,png,jpg',
            'data' => 'required|array',
            'data.*.id' => 'required|exists:notices,id',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            if ($request->hasFile('image')){
                $parent = NoticeParent::findOrFail($request->id);
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image'=>Fa::uploadImage($request->file('image'),'notice')
                ]);
            }
            foreach ($request->data as $data) {
                $notice = Notice::findOrFail($data['id']);
                $notice->update([
                    'name' => $data['name'],
                    //'language_id' => $data['language_id'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Notice updated successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 delete notice
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:notice_parents,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $notice = NoticeParent::findOrFail($request->id);
            Fa::deleteImage($notice?->image);
            $notice->notice()->delete();
            $notice->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Notice deleted successfully'
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
