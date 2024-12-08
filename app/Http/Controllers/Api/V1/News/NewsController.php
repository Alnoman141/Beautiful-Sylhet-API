<?php

namespace App\Http\Controllers\Api\V1\News;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsParent;
use App\Models\NewsType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function type()
    {
        try {
            $types = NewsType::query()
                ->select('id', 'name')
                ->get();
            return response()->json([
                'status_code' => 200,
                'data' => $types
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    public function newsList(Request $request)
    {
        try {
            $news = News::query()
                ->withWhereHas('parent',fn($q) => $q
                    ->with('type')
                    ->where(fn($q) => $q
                        ->where('status',$request->status ?? 1)
                        ->where('news_type_id',$request->news_type_id ?? 1)))
                ->where('language_id', $this->lang)
                ->orderByDesc('id')
                ->paginate($request->integer('per_page',PaginationEnum::Default->value))
                ->through(function ($news) {
                    return [
                        'id' => $news->id,
                        'parent_id' => $news->news_parent_id,
                        'name' => $news->name,
                        'status' => $news?->parent?->status,
                        'news_type_id'=>$news?->parent?->type?->id,
                        'news_type_name'=>$news?->parent?->type?->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $news
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 list
    public function list(Request $request)
    {
        try {
            $news = News::with(['type','parent'])
                ->when($request->filled('news_type_id'),fn($q) => $q->whereHas('parent',fn($q) => $q->where('news_type_id',$request->news_type_id)))
                ->where('language_id', $this->lang)
                ->orderByDesc('id')
                ->paginate($request->integer('per_page',PaginationEnum::Default->value))
                ->through(function ($news) {
                    $news['parent_id']=$news->news_parent_id;
                    $news['status']=$news?->parent?->status;
                    unset($news->parent);
                    return $news;
                });
            return response()->json([
                'status_code' => 200,
                'data' => $news
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 store news
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'news_type_id' => 'required|exists:news_types,id',
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|exists:languages,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message'=>'Validation Error',
                'errors'=>$validate->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $parent = NewsParent::create([
                'news_type_id' => $request->news_type_id,
                'status' => 0,
            ]);
            foreach ( $request->data as $data) {
                $news = new News();
                $news->name = $data['name'];
                $news->news_parent_id = $parent->id;
                $news->language_id = $data['language_id'];
                $news->save();
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'News Created Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'News Creation Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 edit
    public function edit(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:news_parents,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validate->errors(),
            ], 422);
        }
        try {
            $data = NewsParent::with(['news'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'message' => 'News Edit Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25  update news
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|exists:languages,id',
            'data.*.id' => 'required|exists:news,id',
            'news_type_id' => 'nullable|exists:news_types,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validate->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $key=>$data) {
                $news = News::with(['parent'])->find($data['id']);
                if ($news){
                    $news->update([
                        'name' => $data['name'],
                        'language_id' => $data['language_id'],
                    ]);
                    //update news type in parent table
                    if ($key==0 && $request->filled('news_type_id')) {
                        $news->parent->update([
                            'news_type_id' => $request->news_type_id,
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'News Updated Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'News Update Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 11/24/24 publish news
    public function publish(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|exists:news_parents,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validate->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            NewsParent::query()->whereIn('id', $request->ids)->update(['status' => $request->status ?? 1]);
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'News Published Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'News Publish Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-11-25 delete news
    public function delete(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|exists:news_parents,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validate->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            NewsParent::with('news')->whereIn('id', $request->ids)->get()
                ->each(function ($news) {
                    $news->news->each(function ($child) {
                        $child->delete();
                    });
                    $news->delete();
                });
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'News Deleted Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'News Delete Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
}
