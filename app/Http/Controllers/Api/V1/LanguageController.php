<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $languages = Cache::rememberForever('languages',fn()=> Language::query()
                ->when($request->filled('code'),fn($q) => $q->where('code',$request->code))
                ->when($request->filled('id'),fn($q) => $q->where('id',$request->id))
                ->select('id', 'name','code')
                ->get());
            return response()->json([
                'status_code' => 200,
                'data' => $languages
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
