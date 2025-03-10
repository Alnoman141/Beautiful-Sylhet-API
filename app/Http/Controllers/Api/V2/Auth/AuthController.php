<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone|starts_with:01',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        DB::beginTransaction();
        try {
            $name =  $request->phone;
            $email = $request->email ?: $name.'+'.Str::lower(Str::random(10)) . '@gmail.com';
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $email,
                'password' => bcrypt($request->password?:$request->phone),
            ]);
            $user->assignRole(RoleEnum::tourist->value);
            $user->info()->create([
               'data' => $request->except(['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation']),
            ]);
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'User registered successfully',
            ], 200);
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message'=> 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
    //TODO @fa 2025-03-02 authentication login using phone number
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'phone' => 'required|string|starts_with:01|exists:users,phone',
            'password' => 'nullable|string',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        //$credentials = $request->only(['phone', 'password']);
        $credentials = User::query()->where('phone', $request->phone)->first();
        //if (auth()->attempt($credentials)) {
        if ($credentials) {
            PasswordResetToken::updateOrCreate([
                'email' => $request->phone,
            ],['token' => '1234',]);
            return response()->json([
                'status_code' => 200,
                'message' => 'OTP sent successfully',
            ], 200);
        }
        return response()->json([
            'status_code' => 401,
            'message' => 'Unauthorized',
        ], 401);
    }
    //TODO @fa 2025-03-02 verify
    public function verify(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'phone' => 'required|string|starts_with:01',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            if ($request->filled('type') && $request->type == 'register') {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);
                $user->assignRole(RoleEnum::tourist->value);
                $user->info()->create([
                    'data' => $request->only(['nid']),
                ]);
                return response()->json([
                    'status_code' => 200,
                    'message' => 'User registered successfully',
                    'data' => $user,
                ],200);
            }
        }
        return response()->json([
            'status_code' => 200,
            'message' => 'User verified successfully',
            'data' => $user,
        ], 200);
    }
    //TODO @fa 2025-03-10 otp verify
    public function otpVerify(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'phone' => 'required|string|starts_with:01',
            'otp' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        $token = PasswordResetToken::query()->where('email', $request->phone)->first();
        if (!$token) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Phone number not found',
            ], 404);
        }
        if ($token->token == $request->otp) {
            $token->delete();
            $user = User::query()->where('phone', $request->phone)->first();
            $accessToken = $user->createToken('authToken')->accessToken;
            return response()->json([
                'status_code' => 200,
                'message' => 'User logged in successfully',
                'data' => [
                    'token' => $accessToken,
                    'user' => $user,
                ],
            ], 200);
        }
        return response()->json([
            'status_code' => 403,
            'message' => 'OTP not matched',
        ], 403);
    }
}
