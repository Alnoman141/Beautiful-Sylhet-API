<?php

namespace App\Http\Controllers\Api\V1\News;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsParent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function newsList(Request $request)
    {
        try {
            $news = News::query()
                ->where('language_id', $this->lang)
                ->whereHas('parent',fn($q) => $q->where([['status'=>1],['news_type_id',$request->news_type_id ?? 1]]))
                ->orderByDesc('id')
                ->paginate($request->integer('per_page',PaginationEnum::Default->value));
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
            $news = News::with(['type'])
                ->when($request->filled('news_type_id'),fn($q) => $q->whereHas('parent',fn($q) => $q->where('news_type_id',$request->news_type_id)))
                ->where('language_id', $this->lang)
                ->orderByDesc('id')
                ->paginate($request->integer('per_page',PaginationEnum::Default->value));
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
                $news->status = 0;
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
    //TODO @fa 11/24/24 publish news
    public function publish(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|exists:news,id',
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
            NewsParent::query()->whereIn('id', $request->ids)->update(['status' => 1]);
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
}
