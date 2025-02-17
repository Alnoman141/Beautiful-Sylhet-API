<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2025-02-17 12:40 p.m.
 */
namespace App\Http\Controllers\Api\V2\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Hello from RestaurantController',
        ]);
    }
    //TODO @fa 2025-02-17 store restaurant
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|json',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            Restaurant::create([
                'data' => $request->data,
            ]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant created successfully',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-17 update restaurant
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurants,id',
            'data' => 'required|json',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            Restaurant::find($request->id)->update([
                'data' => $request->data,
            ]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Restaurant updated successfully',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-02-17 show restaurant
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:restaurants,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'errors' => $validator->errors(),
            ], 403);
        }
        try {
            $restaurant = Restaurant::findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $restaurant,
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
