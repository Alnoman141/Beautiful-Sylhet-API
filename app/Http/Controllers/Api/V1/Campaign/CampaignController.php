<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    //TODO @fa 2024-11-27 campaign list
    public function index(Request $request)
    {
        try {
            $campaigns = Campaign::with('parent')
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($campaign) {
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name,
                        'description' => $campaign->description,
                        'image' => $campaign?->parent?->name
                    ];
                })
            ;
            return response()->json([
                'status_code' => 200,
                'data' => $campaigns
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-27 campaign details
    public function show(Request $request)
    {
        try {
            $campaign = Campaign::with('parent')
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'description' => $campaign->description,
                    'image' => $campaign?->parent?->name
                ]
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-27 store campaign
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'image'=>'nullable|image',
            'data'=>'required|array',
            'data.*.name'=>'required|string',
            'data.*.language_id'=>'required|integer|exists:languages,id',
            'data.*.description'=>'nullable|string',
        ]);
        if ($validation->fails()){
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors'=>$validation->errors()
            ],422);
        }
        DB::beginTransaction();
        try {
            $parent = CampaignParent::create([
                'image'=>$request->hasFile('image') ? Fa::uploadImage($request->image,'campaign') : null,
            ]);
            foreach ($request->data as $data){
               $parent->campaigns()->create($data);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Campaign created successfully'
            ],200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-27 edit campaign
    public function edit(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'id'=>'required|integer|exists:campaigns,id',
        ]);
        if ($validation->fails()){
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors'=>$validation->errors()
            ],422);
        }
        try {
            $campaign = Campaign::findOrFail($request->id);
            $parent = CampaignParent::with(['campaigns'])->findOrFail($campaign->campaign_parent_id);
            return response()->json([
                'status_code'=>200,
                'data'=>$parent
            ],200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-27 update campaign
    public function update(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'id'=>'required|integer|exists:campaign_parents,id',
            'image'=>'nullable|image',
            'data'=>'required|array',
            'data.*.id'=>'required|integer|exists:campaigns,id',
            'data.*.name'=>'required|string',
            'data.*.language_id'=>'required|integer|exists:languages,id',
            'data.*.description'=>'nullable|string',
        ]);
        if ($validation->fails()){
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors'=>$validation->errors()
            ],422);
        }
        DB::beginTransaction();
        try {
            $parent = CampaignParent::findOrFail($request->id);
            if ($request->hasFile('image')){
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image'=>Fa::uploadImage($request->image,'campaign')
                ]);
            }
            foreach ($request->data as $data){
                Campaign::where('id',$data['id'])->update(collect($data)->only(['name','description','language_id'])
                    ->toArray());
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Campaign updated successfully'
            ],200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-27 delete campaign
    public function destroy(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'id'=>'required|integer|exists:campaigns,id',
        ]);
        if ($validation->fails()){
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation error',
                'errors'=>$validation->errors()
            ],422);
        }
        DB::beginTransaction();
        try {
            $campaign = Campaign::with(['parent'])->findOrFail($request->id);
            Fa::deleteImage($campaign?->parent?->image);
            $campaign->parent()->delete();
            $campaign->delete();
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Campaign deleted successfully'
            ],200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'message'=> 'Campaign cannot be deleted',
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
}
