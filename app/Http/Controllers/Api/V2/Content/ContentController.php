<?php

namespace App\Http\Controllers\Api\V2\Content;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    //TODO @fa 2025-03-10 index content
    public function index()
    {
        $content = Content::query()
            ->latest()
            ->first();
        return response()->json([
            'status_code' => 200,
            'data' => $content,
        ], 200);
    }
    //TODO @fa 2025-03-10 edit content
    public function edit()
    {
        $content = Content::query()
            ->latest()
            ->first();
        return response()->json([
            'status_code' => 200,
            'data' => $content,
        ], 200);
    }
    //TODO @fa 2025-03-10 update content
    public function update(Request $request)
    {
        $content = Content::query()
            ->latest()
            ->first();
        if (!$content) {
            Content::create([
                'data' => $request->data,
            ]);
        }else{
            $content->update([
                'data' => $request->data,
            ]);
        }
        return response()->json([
            'status_code' => 200,
            'message' => 'Content updated successfully',
        ], 200);
    }
    //TODO @fa 2025-03-10 delete content
    public function delete()
    {
        $content = Content::query()
            ->latest()
            ->first();
        if ($content) {
            $content->delete();
        }
        return response()->json([
            'status_code' => 200,
            'message' => 'Content deleted successfully',
        ], 200);
    }
}
