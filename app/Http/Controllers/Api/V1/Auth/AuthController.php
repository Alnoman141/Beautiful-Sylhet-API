<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enum\RoleEnum;
use App\Facade\Alal;
use App\Http\Controllers\Controller;
use App\Jobs\PasswordSentJob;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        try {
            $credentials = $request->only('email', 'password');
            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                if ($user->hasRole(RoleEnum::tourist->value)) {
                    return response()->json([
                        'status_code'=>403,
                        'message'=>'You are not allowed to login',
                    ],403);
                }
                $token = $user->createToken('authToken'
                    ,['*'],now()->addDays(5)
                )->plainTextToken;
                $permissions =  collect($user?->getPermissionsViaRoles())->map(fn($a)=>[
                    'action'=>Str::after($a->name,'-'),
                    'subject'=>Str::before($a->name,'-'),
                ]);
                return response()->json([
                    'status_code'=>200,
                    'message'=>'User logged in successfully',
                    'data'=>[
                        'token'=>$token,
                        'user'=>collect($user)->only(['id','first_name','last_name','email','phone','avatar']),
                        'permissions'=> count($permissions) > 0 ? $permissions : []
                    ]
                ],200);
            } else {
                return response()->json([
                    'status_code'=>403,
                    'message'=>'Email or password is incorrect',
                ],403);
            }
        }catch (\Exception $e) {
            return response()->json([
                'status_code'=>403,
                'message'=>'Something went wrong',
                'errors'=>$e->getMessage(),
            ],403);
        }
    }
    public function forgotPassword(Request $request)
    {
        try {
            $validate =Validator::make ($request->all(),[
                'email' => 'required|email|exists:users,email',
            ],[
                'email.exists' => 'Email not registered'
            ]);
            if ($validate->fails()){
                return response()->json([
                    'status_code' => 422,
                    'message' => 'Validation Error',
                    'error' => $validate->errors()->first(),
                ], 422);
            }
            $token =  Alal::token();
            $user = User::where('email',$request->email)->first();
            $data = [
                'name' => $user->name,
                'subject' => 'Password Reset',
                'password' => $token,
                'email' => $request->email ?? config('fa.alal'),
                'ip'=>$request->ip(),
            ];
            //password reset table create
            PasswordResetToken::updateOrCreate([
                'email' => $request->email,
            ],[
                'token' => $token,
                'created_at' => now()
            ]);
            PasswordSentJob::dispatch($data);
            return response()->json([
                'status_code' => 200,
                'message' => 'Password reset link sent to your email',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2022-11-27 update password
    public function updatePassword(Request $request)
    {
        try {
            $validate =Validator::make ($request->all(),[
                'email' => 'required|email|exists:users,email',
                'token' => 'required',
                'password' => 'required|string|min:8|confirmed',
            ],[
                'email.exists' => 'Email not registered'
            ]);
            if ($validate->fails()){
                return response()->json([
                    'status_code' => 422,
                    'message' => 'Validation Error',
                    'error' => $validate->errors(),
                ], 422);
            }
            $reset = PasswordResetToken::where('email', $request->email)
                ->where('token', $request->token)
                ->first();
            if (!$reset){
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Token not found',
                ], 404);
            }
            $reset->delete();
            $user = User::where('email',$request->email)->first();
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            return response()->json([
                'status_code' => 200,
                'message' => 'Password update success',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2022-11-27 logout
    public function logout()
    {
        try {
            //auth()->user()->tokens()->where('tokenable_id', auth()->user()->id)->delete();
            auth()->user()->currentAccessToken()->delete();
            return response()->json([
                'status_code' => 200,
                'message' => 'Logout Success',
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'status_code' => 403,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2022-11-30 user password verify
    public function verifyPassword(Request $request)
    {
        $validate = Validator::make($request->all(),[
            'id' => 'required|exists:users,id',
            'password' => 'required',
        ],[
            'id.exists' => 'User not found',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'error' => $validate->errors(),
                'message' =>$validate->errors()->first(),
            ],422);
        }
        try {
            $user = User::findOrFail($request->id);
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status_code' => 422,
                    'message' => 'Password Not Match',
                    'error' =>'Validation Error',
                ],422);
            }
            return response()->json([
                'status_code' => 200,
                'message' => 'Password Match',
            ],200);
        }catch (\Exception $exception){
            return response()->json([
                'status_code' => 403,
                'message' => 'Password Verify Failed',
                'error' =>$exception->getMessage(),
            ],403);
        }
    }
}
