<?php

namespace App\Http\Controllers\Api\V1\Notice;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NoticeController extends Controller
{
    //TODO @fa 2024-11-28 notice list
    public function index(Request $request)
    {
        try {
            if ($request->filled('type') && $request->type == 'all') {
                $notices = Notice::query()
                    ->with(['parent.notice'=>fn($q)=>$q
                        ->select(['id','name','notice_parent_id'])
                        ->where('language_id', $this->lang==1?2:1)])
                    ->where('language_id', $this->lang)
                    ->orderByDesc('created_at')
                    ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                    ->through(function ($notice) {
                        return [
                            'id' => $notice->id,
                            'name' => $notice->name,
                            'name_en'=>collect($notice?->parent?->notice)->pluck('name')->first(),
                            'parent_id' => $notice->notice_parent_id,
                            'language_id' => $notice->language_id,
                            'date' => $notice->created_at,
                            'expiry' => $notice?->parent?->expiry,
                        ];
                    });
                return response()->json([
                    'status_code' => 200,
                    'data' => $notices
                ], 200);
            }
            $notices = Notice::query()
                ->with('parent')
                ->when($request->isNotFilled('web'),fn($a)=>$a->whereHas('parent',fn($a)=>$a->where('expiry','>',now()->format('Y-m-d H:i:s'))
                    ->orWhereNull('expiry')
                ))
                ->where('language_id', $this->lang)
                  ->selectRaw('*,DATE(created_at) as date')
                ->orderByDesc('created_at')

                /*->get()->groupBy('date')
               ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'items' => $group->map(function ($notice) {
                        return [
                            'id' => $notice->id,
                            'name' => $notice->name,
                            'parent_id' => $notice->parent_id,
                            'language_id' => $notice->language_id,
                        ];
                    })->toArray(),
                ];
            })->values();*/
            ->paginate($request->integer('per_page', PaginationEnum::Default->value));
            $groupedNotices = $notices->getCollection()->groupBy(function ($item) {
                if ($this->lang == 1) {
                    return self::convertToBanglaNumbers(Carbon::parse($item->date)->locale('bn')->translatedFormat('d F, Y'));
                } else {
                    return Carbon::parse($item->date)->format('d F, Y');
                }
            })
                ->map(function ($item) {
                return $item->map(function ($notice) {
                    return [
                        'id' => $notice->id,
                        'language_id' => $notice->language_id,
                        'parent_id' => $notice->notice_parent_id,
                        'name' => $notice->name,
                        'image' => $notice?->parent?->image,
                        'expiry' => $notice?->parent?->expiry,
                    ];
                });
            });
            $notices->setCollection($groupedNotices);
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
    public static function convertToBanglaNumbers($string)
    {
        $banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($englishDigits, $banglaDigits, $string);
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
            'expiry' => 'nullable|date_format:Y-m-d H:i:s',
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
                'image'=>$request->hasFile('image') ? Fa::uploadImage($request->file('image'),'notice') : null,
                'expiry'=>$request->expiry,
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
            'expiry' => 'nullable|date_format:Y-m-d H:i:s',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 422,
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = NoticeParent::findOrFail($request->id);
            if ($request->hasFile('image')){
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image'=>Fa::uploadImage($request->file('image'),'notice')
                ]);
            }
            $parent->update([
                'expiry'=>$request->expiry,
            ]);
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
            $notice = NoticeParent::with(['notice'])->findOrFail($request->id);
            Fa::deleteImage($notice?->image);
            //$notice->notice()->delete();
            $notice?->notice->each->delete();
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
