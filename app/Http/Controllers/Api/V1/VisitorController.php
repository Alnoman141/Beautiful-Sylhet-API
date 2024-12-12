<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-12-12 11:34
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HotelParent;
use App\Models\LockerParent;
use App\Models\SpotParent;
use App\Models\Visitor;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function store(Request $request)
    {
        try {
            $visitor = Visitor::query()
                ->where('date', now()->format('Y-m-d'))
                ->where('ip', $request->ip())
                ->first();
            if ($visitor) {
                $visitor->increment('count');
            } else {
                Visitor::create([
                    'date' => now()->format('Y-m-d'),
                    'ip' => $request->ip(),
                    'count' => 1,
                ]);
            }
            return response()->json([
                'status_code' => 200,
                'data' => Visitor::query()
                    ->where('date', now()->format('Y-m-d'))
                    ->sum('count'),
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }

    public function index(Request $request)
    {
        try {
            $spot = SpotParent::query()->count();
            $hotel = HotelParent::query()->count();
            $locker = LockerParent::query()->count();
            $visitorToday = Visitor::query()
                ->where('date', now()->format('Y-m-d'))
                ->count();
            $visitorAll = Visitor::query()->count('id');
            return response()->json([
                'status_code' => 200,
                'data' => [
                    'spot' => $spot,
                    'hotel' => $hotel,
                    'locker' => $locker,
                    'visitor_today' => $visitorToday,
                    'visitor_all' => $visitorAll,
                ],
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }
}
