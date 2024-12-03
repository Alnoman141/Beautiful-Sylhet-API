<?php
/**
 *Name: Alauddin F-a
 *Designation: Sr. Software Engineer
 *Date: 2024-11-28 11:52 a.m.
 */
namespace App\Http\Controllers\Api\V1\Bank;

use App\Enum\PaginationEnum;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankAtm;
use App\Models\BankAtmParent;
use App\Models\BankParent;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    //TODO @fa 2024-11-28 bank list
    public function bankList(Request $request)
    {
        try {
            $banks = Bank::query()
                ->where('language_id', $this->lang)
                ->get()
                ->map(function ($bank) {
                    return [
                        'id' => $bank->id,
                        'parent_id' => $bank->bank_parent_id,
                        'name' => $bank->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $banks
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }

    //TODO @fa 2024-11-28 bank details
    public function bankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:banks,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $bank = Bank::with(['parent'])->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $bank
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'errors' => $exception->getMessage()
            ], 403);
        }
    }

    //TODO @fa 2024-11-28 bank store
    public function bankStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.name' => 'required|string',
            'data.*.language_id' => 'required|integer|exists:languages,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $parent = BankParent::create([]);
            foreach ($request->data as $data) {
                Bank::create([
                    'bank_parent_id' => $parent->id,
                    'name' => $data['name'],
                    'language_id' => $data['language_id']
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Bank created successfully'
            ], 200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-28 bank edit
    public function bankEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists::bank_parents,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $bank = Bank::query()
                ->where('bank_parent_id', $request->id)->get();
            return response()->json([
                'status_code' => 200,
                'data' => $bank
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-28 bank update
    public function bankUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.id' => 'required|integer|not_in:0|exists:banks,id',
            'data.*.name' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            foreach ($request->data as $data) {
                $bank = Bank::findOrFail($data['id']);
                $bank->update([
                    'name' => $data['name']
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Bank updated successfully'
            ], 200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-28 bank delete
    public function bankDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:bank_parents,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        DB::beginTransaction();
        try {
            $bank = BankParent::with(['banks'])->findOrFail($request->id);
            $bank->banks()->delete();
            $bank->delete();
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Bank deleted successfully'
            ], 200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }

    //TODO @fa 2024-11-28 atm list
    public function atmList(Request $request)
    {
        try {
            $banks = BankAtm::with(['spot.translate'])
                ->when($request->filled('name'),fn($query) => $query->where('name','like','%'.$request->name.'%'))
                ->when($request->filled('spot_parent_id'),fn($a)=>$a->whereHas('parent',fn($b)=>$b->where('spot_parent_id',$request->spot_parent_id)))
                ->when($request->filled('bank_parent_id'),fn($a)=>$a->whereHas('parent',fn($b)=>$b->where('bank_parent_id',$request->bank_parent_id)))
                ->where('language_id', $this->lang)
                ->paginate($request->integer('per_page',PaginationEnum::Default->value))
                ->through(function ($bank) {
                    return [
                        'id' => $bank->id,
                        'parent_id'=>$bank->bank_atm_parent_id,
                        'name' => $bank->name,
                        'spot_id' => $bank?->spot?->id,
                        'spot_name' => $bank?->spot?->translate?->name ?? '',
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $banks
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-28 atm details
    public function atmDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:bank_atms,id'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 403);
        }
        try {
            $bank = BankAtm::with(['parent', 'spot' => fn($a) => $a->with(['translate' => fn($b) => $b->where('language_id', $this->lang)])])
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $bank
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'errors' => $exception->getMessage()
            ], 403);
        }
    }
    //TODO @fa 2024-11-28 atm store
    public function atmStore(Request $request){
        $validator = Validator::make($request->all(),[
            'spot_parent_id'=>'nullable|integer|exists:spot_parents,id',
            'bank_parent_id'=>'nullable|integer|exists:bank_parents,id',
            'map_link'=>'nullable|string',
            'image'=>'nullable|image|mimes:jpeg,png,jpg',
            'data'=>'required|array',
            'data.*.name'=>'required|string',
            'data.*.language_id'=>'required|integer|exists:languages,id',
            'data.*.address'=>'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation error',
                'errors'=>$validator->errors()
            ],403);
        }
        DB::beginTransaction();
        try {
            $parent = BankAtmParent::create([
                'bank_parent_id'=>$request->bank_parent_id,
                'spot_parent_id'=>$request->spot_parent_id,
                'map_link'=>$request->map_link,
                'image'=>$request->hasFile('image') ? Fa::uploadImage($request->file('image'),'bank-atm') : null,
            ]);
            foreach ($request->data as $data){
                BankAtm::create([
                    'bank_atm_parent_id'=>$parent->id,
                    'name'=>$data['name'],
                    'language_id'=>$data['language_id'],
                    'address'=>@$data['address'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Bank Atm created successfully'
            ],200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-28 edit atm
    public function atmEdit(Request $request){
        $validator = Validator::make($request->all(),[
            'id'=>'required|integer|exists:bank_atms,id'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation error',
                'errors'=>$validator->errors()
            ],403);
        }
        try {
            $parent = BankAtmParent::with(['atms'])->findOrFail($request->id);
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
    //TODO @fa 2024-11-28 update atm
    public function atmUpdate(Request $request){
        $validator = Validator::make($request->all(),[
            'id'=>'required|integer|exists:bank_atm_parents,id',
            'map_link'=>'nullable|string',
            'image'=>'nullable|image|mimes:jpeg,png,jpg',
            'spot_parent_id'=>'nullable|not_in:0|integer|exists:spot_parents,id',
            'bank_parent_id'=>'nullable|not_in:0|integer|exists:bank_parents,id',
            'data'=>'required|array',
            'data.*.id'=>'required|integer|exists:bank_atms,id',
            'data.*.name'=>'required|string',
            'data.*.address'=>'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation error',
                'errors'=>$validator->errors()
            ],403);
        }
        DB::beginTransaction();
        try {
            $parent = BankAtmParent::findOrFail($request->id);
            $parent->update([
                'map_link'=>$request->map_link ?? $parent->map_link,
                'spot_parent_id'=>$request->spot_parent_id ?? $parent->spot_parent_id,
            ]);
            if ($request->hasFile('image')){
                Fa::deleteImage($parent?->image);
                $parent->update([
                    'image'=>Fa::uploadImage($request->file('image'),'bank-atm')
                ]);
            }
            foreach ($request->data as $data){
                $bank = BankAtm::findOrFail($data['id']);
                $bank->update([
                    'name'=>$data['name'],
                    'address'=>$data['address'],
                ]);
            }
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Bank Atm updated successfully'
            ],200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-11-28 atm delete
    public function atmDelete(Request $request){
        $validator = Validator::make($request->all(),[
            'id'=>'required|integer|exists:bank_atm_parents,id'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status_code'=>403,
                'message'=>'Validation error',
                'errors'=>$validator->errors()
            ],403);
        }
        DB::beginTransaction();
        try {
            $parent = BankAtmParent::with(['atms'])->findOrFail($request->id);
            $parent->atms()->delete();
            $parent->delete();
            DB::commit();
            return response()->json([
                'status_code'=>200,
                'message'=>'Bank Atm deleted successfully'
            ],200);
        }catch (\Exception $exception){
            DB::rollBack();
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
    //TODO @fa 2024-12-03 bank dropdown
    public function bankDropdown(Request $request){
        try {
            $banks = Bank::query()
                ->where('language_id', $this->lang)
                ->get()
                ->map(function ($bank) {
                    return [
                        'id' => $bank->bank_parent_id,
                        'name' => $bank?->name,
                    ];
                })
            ;
            return response()->json([
                'status_code' => 200,
                'data' => $banks
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code'=>403,
                'errors'=>$exception->getMessage()
            ],403);
        }
    }
}
