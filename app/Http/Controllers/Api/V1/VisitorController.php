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
use App\Models\TransportTypeParent;
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
            $transports =TransportTypeParent::query()
                ->with(['translate'=>fn($a)=>$a->where('language_id',$this->lang)])
                ->withCount('transport')
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item?->translate?->name,
                        'count' => $item->transport_count
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => [
                    'spot' => $spot,
                    'hotel' => $hotel,
                    'locker' => $locker,
                    'transports' => $transports,
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
