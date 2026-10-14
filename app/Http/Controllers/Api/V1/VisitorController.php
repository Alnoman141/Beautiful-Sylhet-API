<?php
/**
 *Name: Abdullah Al Noman
 *Designation: Sr. Software Engineer
 *Date: 2024-12-12 11:34
 */

namespace App\Http\Controllers\Api\V1;

use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\HotelParent;
use App\Models\LockerParent;
use App\Models\SpotParent;
use App\Models\TransportTypeParent;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Fa;
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
            $spot = SpotParent::query()->whereHas('spot')->count();
            $hotel = HotelParent::query()->whereHas('hotels')->count();
            $locker = LockerParent::query()->whereHas('lockers')->count();
            $visitorToday = Visitor::query()
                ->where('date', now()->format('Y-m-d'))
                ->count();
            $visitorAll = Visitor::query()->count('id');
            $todayRegistered = User::query()
                ->whereHas('roles',fn($q)=>$q->where('name',RoleEnum::tourist->value))
               ->whereDate('created_at', now()->format('Y-m-d'))
                ->count();
            $allRegistered = User::query()
                ->whereHas('roles',fn($q)=>$q->where('name',RoleEnum::tourist->value))
                ->count();
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
                    'today_registered' => $todayRegistered,
                    'all_registered' => $allRegistered,
                ],
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ],403);
        }
    }

    public function appInformation()
    {
        $app = Fa::appDownloadAndroid();
        $content = Content::query()->latest()->first();
        return response()->json([
            'status_code'=>200,
            'app'=>$app,
            'content'=>$content
        ],200);
    }
}
