<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Fa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
                if ($user->avatar) {
                    $path = public_path($user->avatar);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
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
}
