<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enum\PaginationEnum;
use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function avatarUpload(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'avatar' => 'required|file|image|mimes:jpeg,png,jpg|max:5120',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validate->errors()->first(),
                'error' => 'Validation Error',
            ], 422);
        }
        try {
            $user = User::findOrFail($request->id);
            //TODO @fa 2022-11-02 before avatar delete
            if ($request->hasFile('avatar')) {
                Fa::deleteImage($user?->avatar);
                $user->avatar = Fa::uploadImage($request->file('avatar'), 'avatar');
            }
            $user->save();
            return response()->json([
                'status_code' => 200,
                'message' => 'Avatar Upload Successfully',
                'data' => collect($user)->only('id', 'avatar')
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Avatar Upload Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }

    //TODO @fa 2022-11-30 passwordUpdate user
    public function passwordUpdate(Request $request)
    {
        $old_password = $request->filled('id') && $request->id == Auth::id() ? 'required' : 'nullable';
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'old_password' => $old_password,
            'password' => 'required|confirmed|min:8|different:old_password',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => $validate->errors()->first(),
                'errors' => $validate->errors(),
            ], 422);
        }
        try {
            $user = User::findOrFail($request->id);
            if ($old_password == 'required') {
                if (!Hash::check($request->old_password, $user->password)) {
                    return response()->json([
                        'status_code' => 422,
                        'message' => 'Validation Error',
                        'errors' => 'Old Password Not Match',
                    ], 422);
                } else {
                    $user->password = $request->password;
                }
            }
            else{
                $user->password = $request->password;
            }
            $user->save();
            return response()->json([
                'status_code' => 200,
                'message' => 'Password Update Successfully',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Password Update Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-12-04 list user
    public function index(Request $request)
    {
        try {
            $users = User::with('roles')
                ->when($request->filled('name'), fn($q) => $q
                    ->where(fn($q) => $q
                        ->whereRaw('CONCAT(first_name," ",last_name) like ?', ['%' . $request->name . '%'])
                        ->orWhere('phone', 'like', '%' . $request->name . '%')
                        ->orWhere('email', 'like', '%' . $request->name . '%')
                    )
                )
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'role' => $user->roles->first()?->name,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $users,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'message' => 'User List Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-12-04 register user
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->whereNull('deleted_at'),
                'starts_with:01',
            ],
            'email' =>[
                'required',
                'max:255',
                'email:rfc,dns',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => 'required|confirmed|min:8',
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
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $user->assignRole($request->role ?? RoleEnum::editor->value);
            if ($request->hasFile('avatar')) {
                $user->update([
                    'avatar' => Fa::uploadImage($request->avatar, 'avatar')
                ]);
            }
            //TODO @fa 2024-12-08
            $content = [
                'data' => [
                    'values' => '',
                    'action' => 'create',
                    'title'=>$user->name .' user register',
                ],
            ];
            Fa::activity($content);
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'User Register Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'User Register Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-12-04 user update
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->whereNull('deleted_at')->ignore($request->id),
                'starts_with:01',
            ],
            'email' => [
                'required',
                'max:255',
                'email:rfc,dns',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($request->id),
            ],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
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
            $user = User::findOrFail($request->id);
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);
            if ($request->filled('role')) {
                $user->syncRoles($request->role);
            }
            if ($request->hasFile('avatar')){
                Fa::deleteImage($user?->avatar);
                $user->update([
                    'avatar' => Fa::uploadImage($request->avatar, 'avatar')
                ]);
            }
            //which data update then log
            //TODO @fa 2024-12-08 log
            //which column value change get data
            $data = collect($user->getChanges())->map(function ($value, $key) use ($user) {
                return [
                    'column' => $key,
                    'old' => $user->getOriginal($key),
                    'new' => $value,
                ];
            });
            $content = [
                'data' => [
                    'values' => $data,
                    'action' => 'update',
                    'title'=>$user->name .' information update',
                ],
            ];
            Fa::activity($content);
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'User Update Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'User Update Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-12-04 user delete
    public function delete(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
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
            $user = User::findOrFail($request->id);
            Fa::deleteImage($user?->avatar);
            $user->delete();
            $content = [
                'data' => [
                    'values' => '',
                    'action' => 'delete',
                    'title'=>$user->name .' user delete',
                ],
            ];
            Fa::activity($content);
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'User Delete Successfully',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'User Delete Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-12-04 edit user
    public function edit(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation Error',
                'errors' => $validate->errors(),
            ], 422);
        }
        try {
            $user = User::with('roles')->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $user,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'message' => 'User Edit Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2024-12-08 activity log
    public function activity(Request $request)
    {
        try {
            $logs = \App\Models\Log::with('user')
                ->when($request->filled('name'), fn($q) => $q
                    ->where(fn($q) => $q
                        ->whereRaw('CONCAT(first_name," ",last_name) like ?', ['%' . $request->name . '%'])
                        ->orWhere('phone', 'like', '%' . $request->name . '%')
                        ->orWhere('email', 'like', '%' . $request->name . '%')
                    )
                )
                ->paginate($request->integer('per_page', PaginationEnum::Default->value))
                ->through(function ($log) {
                    return [
                        'id' => $log->id,
                        'name' => $log?->user?->name,
                        'content' => $log->content,
                        'created_at' => $log->created_at,
                    ];
                });
            return response()->json([
                'status_code' => 200,
                'data' => $logs,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Activity Log Failed',
                'errors' => $exception->getMessage(),
            ], 403);
        }
    }
}
