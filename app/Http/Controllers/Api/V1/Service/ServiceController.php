<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $services = ServiceParent::query()
                ->where('type',$request->type ?? 1)
                ->with(['services'=>fn($a)=>$a
                    ->with('spots:spots.id as id,name,language_id,spots.spot_parent_id as parent_id')
                ])
                ->get()
                ->map(function ($item) use ($request){
                    return [
                        'id'=>$item->id,
                        'descriptions'=> $request->filled('app') ? $item->descriptions : collect($item->descriptions)->where('language_id',$this->lang)->first(),
                        'link'=>$item->link,
                        'type'=>$item->type,
                        'services'=> $request->filled('app') ? collect($item->services)->map(function ($item) use($request){
                            if (in_array($request->type,[1,2]))
                            {
                                $b = collect($item->booking_numbers)->first();
                                //$s = collect($item->supervisor_numbers)->first();
                                return [
                                    'name'=>@$b['name'] ?? '',
                                    'number'=>isset($b['numbers']) ? collect($b['numbers'])->first() : '',
                                    /* 's_name'=>$s['name'],
                                     's_number'=>collect($s['numbers'])->first(),*/
                                    'owner_information'=>$item->owner_information,
                                ];
                            }
                            else{
                                return [
                                    'id'=>$item->id,
                                    'spots'=>collect($item->spots)->map(function ($item){
                                        return [
                                            'id'=>$item->id,
                                            'parent_id'=>$item->parent_id,
                                            'name'=>$item->name,
                                            'language_id'=>$item->language_id,
                                        ];
                                    }),
                                    'booking_numbers'=>$item->booking_numbers,
                                    'supervisor_numbers'=>$item->supervisor_numbers,
                                    'owner_information'=>$item->owner_information,
                                ];
                            }
                        }) : collect($item->services)->map(function ($item){
                            return [
                                'id'=>$item->id,
                                'spots'=>collect($item->spots)->where('language_id',$this->lang)->first(),
                                'booking_numbers'=>$item->booking_numbers,
                                'supervisor_numbers'=>$item->supervisor_numbers,
                                'owner_information'=>$item->owner_information,
                            ];
                        }),
                    ];
                })->values()->first();
            return response()->json([
                'status_code'=>200,
                'data'=>$services],200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=>403,
                'message'=>'Data not found',
                'error'=>$e->getMessage()],403);
        }
    }
    public function indexOLD(Request $request)
    {
        try {
            $data = Service::with(['parent'=>fn($a)=>$a
                ->where('type',$request->type ?? 1)
                //->whereJsonContains('descriptions->*.language_id', $this->lang)
                ,
                'spots:spots.id as id,name,language_id,spots.spot_parent_id as parent_id',
                /*'spot'=>fn($a)=>$a->where('language_id',$this->lang)
                    ->select('spots.id as id','spots.spot_parent_id as spot_parent_id','name')*/
            ])->get()
                ->map(function ($item) use ($request){
                    return [
                        'id'=>$item->id,
                        'descriptions'=> $request->filled('app') ? $item?->parent?->descriptions : collect($item?->parent?->descriptions)->where('language_id',$this->lang)->first(),
                        'link'=>$item?->parent?->link,
                        'type'=>$item?->parent?->type,
                        'spots'=> $request->filled('app') ? collect($item?->spots)->map(function ($item){
                            return [
                                'id'=>$item->id,
                                'parent_id'=>$item->parent_id,
                                'name'=>$item->name,
                                'language_id'=>$item->language_id,
                            ];
                        }) : collect($item?->spots)->where('language_id',$this->lang)->first(),
                           // ->where('language_id',$this->lang)->first(),
                        //'name_en'=>$item?->spot?->name,
                        'booking_numbers'=>$item->booking_numbers,
                        'supervisor_numbers'=>$item->supervisor_numbers,
                    ];
                });
            return response()->json([
                'status_code'=>200,
                'data'=>$data],200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=>403,
                'message'=>'Data not found',
                'error'=>$e->getMessage()],403);
        }
    }

    public function type(Request $request)
    {
        try {
            $data = ServiceParent::query()
                ->where('type',$request->type ?? 1)
                ->when($request->filled('id'),fn($a)=>$a->where('id',$request->id))
                ->first();
            return response()->json([
                'status_code'=>200,
                'data'=>$data],200);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Data not found',
                'error' => $e->getMessage()], 403);
        }
    }

    public function updateService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:service_parents,id',
            'descriptions' => 'required',
            'link' => 'nullable',
            'type' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation Error',
                'error'=>$validator->errors()],422);
        }
        try {
            $service = ServiceParent::findOrFail($request->id);
            if ($request->hasFile('image')){
                Fa::deleteImage($service?->image);
                $service->update([
                    'image'=>Fa::uploadImage($request->file('image'),'service'),
                ]);
            }
            $service->update([
                'descriptions'=>$request->descriptions,
                'link'=>$request->link ?? null,
                'type'=>$request->type ?? 1,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Data not found',
                'error' => $e->getMessage()], 403);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'descriptions' => 'required', //
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'booking_numbers' => 'nullable|array',
            'supervisor_numbers' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation Error',
                'error'=>$validator->errors()],403);
        }
        DB::beginTransaction();
        try {
            $parent = ServiceParent::where('type',$request->type ?? 1)
                ->first();
            if (!$parent){
                $parent = ServiceParent::firstOrCreate([
                    'descriptions'=>$request->descriptions,
                    'link'=>$request->link ?? null,
                    'type'=>$request->type ?? 1,
                    'image'=>$request->hasFile('image') ? Fa::uploadImage($request->file('image'),'service') : null,
                ]);
            }
            Service::create([
                'service_parent_id'=>$parent->id,
                'spot_parent_id'=>$request->spot_parent_id ?? null,
                'booking_numbers'=>$request->booking_numbers,
                'supervisor_numbers'=>$request->supervisor_numbers,
                'owner_information'=>$request->owner_information ?? null,
            ]);
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Data created successfully',
                ],200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'message'=>'Creation failed',
                'error'=>$e->getMessage()],403);
        }
    }

    public function show(Request $request)
    {
        try {
            $data = Service::with(['parent',
                'spot'=>fn($a)=>$a->where('language_id',$this->lang)
                    ->select('spots.id as id','spots.spot_parent_id as spot_parent_id','name')
            ])->findOrFail($request->id);
            return response()->json([
                'status_code'=>200,
                'data'=>$data],200);
        }catch (\Exception $e){
            return response()->json([
                'status_code'=>403,
                'message'=>'Data not found',
                'error'=>$e->getMessage()],403);
        }
    }
    //TODO @fa 2024-12-19 update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services,id',
            'descriptions' => 'required',
            'spot_parent_id' => 'nullable|exists:spot_parents,id',
            'booking_numbers' => 'nullable|array',
            'supervisor_numbers' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation Error',
                'error'=>$validator->errors()],403);
        }
        DB::beginTransaction();
        try {
            $service = Service::findOrFail($request->id);
            $service->update([
                'spot_parent_id'=>$request->spot_parent_id ?? null,
                'booking_numbers'=>$request->booking_numbers,
                'supervisor_numbers'=>$request->supervisor_numbers,
                'owner_information'=>$request->owner_information ?? null,
            ]);
            $service->parent->update([
                'descriptions'=>$request->descriptions,
                'link'=>$request->link ?? null,
                'type'=>$request->type ?? 1,
            ]);
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Data updated successfully',
                ],200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'message'=>'Update failed',
                'error'=>$e->getMessage()],403);
        }
    }
    //TODO @fa 2024-12-19 delete
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:services,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation Error',
                'error'=>$validator->errors()],403);
        }
        DB::beginTransaction();
        try {
             Service::destroy($request->id);
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Data deleted successfully',
                ],200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'message'=>'Delete failed',
                'error'=>$e->getMessage()],403);
        }
    }
}
