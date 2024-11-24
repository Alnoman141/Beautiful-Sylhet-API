<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //TODO @fa 11/24/24 login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ],[
            'email.exists' => 'The email address is not registered',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status_code'=>422,
                'message'=>'Validation Error',
                'errors'=>$validator->errors(),
            ],422);
        }
        $credentials = $request->only('email', 'password');
        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('authToken'
                ,['*'],now()->addDays(5)
            )->plainTextToken;
            return response()->json([
                'status_code'=>200,
                'message'=>'User logged in successfully',
                'data'=>[
                    'token'=>$token,
                    'user'=>collect($user)->only(['id','first_name','last_name','email','phone','avatar']),
                    'permissions'=>collect($user->getPermissionsViaRoles())->pluck('name')
                ]
            ],200);
        } else {
            return response()->json([
                'status_code'=>500,
                'message'=>'Unauthorized',
            ]);
        }
    }
}
