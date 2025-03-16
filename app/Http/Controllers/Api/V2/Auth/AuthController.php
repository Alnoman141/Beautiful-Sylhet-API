<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Enum\PaginationEnum;
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
            $name = $request->phone;
            $email = $request->email ?: $name . '+' . Str::lower(Str::random(10)) . '@gmail.com';
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $email,
                'password' => bcrypt($request->password ?: $request->phone),
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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    //TODO @fa 2025-03-02 authentication login using phone number
    public function login(Request $request)
    {
        //check phone or email
        $check = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        //if phone then required phone and start with 01 otherwise email
        $required = $check == 'phone' ? 'required|string|starts_with:01|exists:users,phone' : 'required|email|exists:users,email';
        $validate = Validator::make($request->all(), [
            'phone' =>$required, //'required|string|starts_with:01|exists:users,phone',
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
        $credentials = User::query()->where($check, $request->phone)->first();
        //if (auth()->attempt($credentials)) {
        if ($credentials) {
            PasswordResetToken::updateOrCreate([
                'email' => $request->phone,
            ], ['token' => '1234',]);
            return response()->json([
                'status_code' => 200,
                'message' => 'OTP sent successfully to your '.$check,
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
                ], 200);
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
        $check = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $required = $check == 'phone' ? 'required|string|starts_with:01|exists:users,phone' : 'required|email|exists:users,email';
        $validate = Validator::make($request->all(), [
            'phone' => $required,
            'otp' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        $token = PasswordResetToken::query()->where($check, $request->phone)->first();
        if (!$token) {
            return response()->json([
                'status_code' => 404,
                'message' => $check=='phone'?'Phone number not found':'Email not found',
            ], 404);
        }
        if ($token->token == $request->otp) {
            $token->delete();
            $user = User::query()->where($check, $request->phone)->first();
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

    //TODO @fa 2025-03-11 list
    public function list(Request $request)
    {
        try {
            $users = User::query()
                ->whereHas('roles', fn($q) => $q->where('name', RoleEnum::tourist->value))
                ->when($request->name, fn($q) => $q
                    ->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$request->name%"])
                    ->orWhereAny(['phone', 'email'], 'like', '%' . $request->name . '%')
                )
                ->with('info')
                ->paginate($request->integer('per_page') ?? PaginationEnum::Default->value);
            return response()->json([
                'status_code' => 200,
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }

    //TODO @fa 2025-03-11 show
    public function show(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        try {
            $user = User::query()
                ->whereHas('roles', fn($q) => $q->where('name', RoleEnum::tourist->value))
                ->with('info')
                ->findOrFail($request->id);
            return response()->json([
                'status_code' => 200,
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 403,
                'errors' => $e->getMessage(),
            ], 403);
        }
    }
}
