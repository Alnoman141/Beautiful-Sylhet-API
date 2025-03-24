<?php

namespace App\Http\Controllers\Api\V2\Auth;

use App\Enum\PaginationEnum;
use App\Enum\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\AuthTokenNotification;
use App\Services\Fa;
use App\Services\SmsGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => $request->is_bangladeshi ? 'nullable|email' : 'required|email',
            'phone' => $request->is_bangladeshi ? 'required|string|starts_with:01' : 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status_code' => 422, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $identifier = $request->is_bangladeshi ? 'phone' : 'email';
        $columnValue = $request->{$identifier};
        if ($request->isNotFilled('type')) {
            $type = $identifier== 'phone' && $request->is_bangladeshi==1 ? 'sms' : 'email';
            $request->merge(['type' => $type]);
        }
        elseif ($request->filled('type')) {
            $identifier = $request->type == 'sms' ? $request->phone : $request->email;
        }
        if ($request->filled('is_bangladeshi') && $request->is_bangladeshi == 0 && $request->type=='sms') {
            if ($request->isNotFilled('phone')){
                return response()->json(['status_code' => 422,
                    'message' => 'Validation error',
                    'errors' => ['phone' => ['The phone field is required.']],
                ], 422);
            }
        }
        if ($request->filled($identifier)) {
            $existingUser = User::query()
                ->where($identifier, $columnValue)
                ->when($identifier=='phone'&& $request->filled('email'), fn($q) => $q->orWhere('email', $request->email))
                ->when($identifier=='email'&& $request->filled('phone'), fn($q) => $q->orWhere('phone', $request->phone))
                ->first();
            if ($existingUser) {
                if ($existingUser && $existingUser->email_verified_at == null) {
                    $token = config('app.env') == 'production' ? Fa::token() : '123456';
                    if (config('app.env') == 'production') {
                        if ($request->type == 'sms') {
                            $status = SmsGateway::send($request->phone, $token);
                            if (@$status['code'] != 200) {
                                return response()->json(['status_code' => 403, 'message' => $status['message']], 403);
                            }
                        } else {
                            Notification::route('mail', $request->email)->notify(new AuthTokenNotification($token));
                        }
                    }

                    PasswordResetToken::updateOrCreate([
                        'email' => $columnValue,
                    ], ['token' => $token, 'created_at' => now()]);
                    return response()->json(['status_code' => 200,
                        'message' => 'OTP sent successfully to your ' . $identifier], 200);
                }
                else{
                    $message = 'Account already exists with this ' .
                        ($existingUser->email === $request->email && $existingUser->phone === $request->phone ? 'email and phone' : ($existingUser->email === $request->email ? 'email' : 'phone'));
                    return response()->json([
                        'status_code' => 403,
                        'message' => $message,
                    ], 403);
                }
            }
        }
        //return $request->all();
        DB::beginTransaction();
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => bcrypt($request->password ?: $request->phone),
                'avatar' => $request->hasFile('avatar') ? Fa::uploadFile($request->file('avatar'), 'avatar') : null,
            ]);

            $user->assignRole(RoleEnum::tourist->value);
            $user->info()->create(['data' => $request->except(['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation'])]);

            $token = config('app.env') == 'production' ? Fa::token() : '123456';
            if (config('app.env') == 'production') {
                if ($request->type == 'sms') {
                    $status = SmsGateway::send($request->phone, $token);
                    if (@$status['code'] != 200) {
                        return response()->json(['status_code' => 403, 'message' => $status['message']], 403);
                    }
                } else {
                    Notification::route('mail', $request->email)->notify(new AuthTokenNotification($token));
                }
            }

            PasswordResetToken::updateOrCreate([
                'email' => $columnValue,
            ], ['token' => $token, 'created_at' => now()]);

            DB::commit();
            return response()->json(['status_code' => 200, 'message' => 'OTP sent successfully to your ' . $identifier], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status_code' => 403,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ], 403);
        }
    }
    public function registerOld(Request $request)
    {
        $email = $request->is_bangladeshi == 0 ? 'required|email' : 'nullable|email';
        $phone = $request->is_bangladeshi == 1 ? 'required|string|starts_with:01' : 'nullable|string';
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'email' => $email,
            'phone' => $phone,
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        $check = $request->is_bangladeshi==0?'email':'phone';
        $column = $check=='phone' ? $request->phone : $request->email;
        if ($request->filled('is_bangladeshi')){
            try {
                //phone check already exists
                if ($request->filled('phone')){
                    $phoneFind = User::query()->where('phone', $request->phone)->first();
                    if ($phoneFind){
                        return response()->json([
                            'status_code' => 403,
                            'message' => 'Account already exists with this phone',
                        ], 403);
                    }
                }
                if ($request->filled('email')){
                    $emailFind = User::query()->where('email', $request->email)->first();
                    if ($emailFind){
                        return response()->json([
                            'status_code' => 403,
                            'message' => 'Account already exists with this email',
                        ], 403);
                    }
                }
                $exists = User::query()->where($check,$column)->first();
                if ($exists && $exists->email_verified_at != null){
                    return response()->json([
                        'status_code' => 403,
                        'message' => 'Account already exists with this '.$check,
                    ], 403);
                }elseif ($exists && $exists->email_verified_at == null){
                    $token = config('app.env') == 'production' ? Fa::token() : "123456";
                    if (config('app.env') == 'production') {
                        if ($check == 'phone') {
                            $status = SmsGateway::send($request->phone, $token);
                            if (@$status['code'] != 200) {
                                return response()->json([
                                    'status_code' => 403,
                                    'message' => $status['message'],
                                ], 403);
                            }
                        } else {
                            Notification::route('mail', $request->email)->notify(new AuthTokenNotification($token));
                        }
                    }
                    PasswordResetToken::updateOrCreate([
                        'email' => $column,
                    ], ['token' => $token,'created_at'=>now()]);
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'Otp sent successfully to your ' . $check,
                    ], 200);
                }
            }catch (\Exception $e) {
                return response()->json([
                    'status_code' => 403,
                    'message' => $e->getMessage(),
                ], 403);
            }
        }
        DB::beginTransaction();
        try {
            $email = $request->email;
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $email,
                'password' => bcrypt($request->password ?: $request->phone),
                'avatar' => $request->hasFile('avatar') ? Fa::uploadFile($request->file('avatar'), 'avatar') : null,
            ]);
            $user->assignRole(RoleEnum::tourist->value);
            $user->info()->create([
                'data' => $request->except(['first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation']),
            ]);
            //$accessToken = $user->createToken('authToken')->plainTextToken;
            $check = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $token = config('app.env') == 'production' ? Fa::token() : "123456";
            if ($request->isNotFilled('type')) {
                $type = $check == 'phone' ? 'sms' : 'email';
                $request->merge(['type' => $type]);
            }
            if (config('app.env') == 'production') {
                if ($request->type == 'sms') {
                    $status = SmsGateway::send($request->phone, $token);
                    if (@$status['code'] != 200) {
                        return response()->json([
                            'status_code' => 403,
                            'message' => $status['message'],
                        ], 403);
                    }
                } else {
                    Notification::route('mail', $request->phone)->notify(new AuthTokenNotification($token));
                }
            }
            PasswordResetToken::updateOrCreate([
                'email' => $column,
            ], ['token' => $token,'created_at'=>now()]);
            DB::commit();
            return response()->json([
                'status_code' => 200,
                'message' => 'Otp sent successfully to your ' . $check,
               /* 'data' => [
                    'token' => $accessToken,
                    'user' => $user,
                ],*/
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 403,
                'message' => $e->getMessage(),
                //'errors' => $e->getMessage(),
            ], 403);
        }
    }

    //TODO @fa 2025-03-02 authentication login using phone number
    public function login(Request $request)
    {
        //check phone or email
        $check = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
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
            if (!$credentials->hasRole(RoleEnum::tourist->value)) {
                return response()->json([
                    'status_code' => 403,
                    'message' => "You don't have permission to login",
                ], 403);
            }
            if ($request->isNotFilled('type')){
                $type = $check=='phone'?'sms':'email';
                $request->merge(['type'=>$type]);
            }
            try {
                $token = self::sendToken($credentials,$request->type);
                if ($token['code']==200){
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'OTP sent successfully to your '.$check,
                        'token'=>$token['token']
                    ], 200);
                }
                else{
                    return response()->json([
                        'status_code' => 403,
                        'message' => $token['message'],
                    ], 403);
                }
            }catch (\Exception $e){
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage(),
                ], 403);
            }
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
        $user = User::query()->where('phone', $request->phone)->first();
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
        $token = PasswordResetToken::query()->where('email', $request->phone)
            ->whereNull('created_at')
            ->first();
        if (!$token) {
            return response()->json([
                'status_code' => 404,
                'message' => $check=='phone'?'Phone number not found':'Email not found',
            ], 404);
        }
        if ($token->token == $request->otp) {
            $token->delete();
            $user = User::query()->where($check, $request->phone)->first();
            if ($user->email_verified_at == null) {
                $user->update([
                    'email_verified_at' => now(),
                ]);
            }
            $accessToken = $user->createToken('authToken')->plainTextToken;
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

    private function sendToken($user,$type)
    {
        $token = config('app.env') == 'production' ? Fa::token() : "123456";
        //\Illuminate\Support\Facades\Log::info('token: '.$token);
        if ($type=='sms')
        {
            if (config('app.env') == 'production'){
                $status = SmsGateway::send($user->phone,$token);
                if (@$status['code']!=200){
                    return [
                        'code'=>403,
                        'message'=>$status['message']
                    ];
                }
            }
        }else{
            Notification::route('mail', $user->email)->notify(new AuthTokenNotification($token));
        }
        PasswordResetToken::updateOrCreate([
            'email' => $type=='sms'?$user->phone:$user->email,
        ], ['token' => $token,'created_at'=>null]);
        return [
            'code'=>200,
            'token'=> config('app.env') == 'local' ? $token : null,
        ];
    }
    //TODO @fa 2025-03-20 user verification
    public function verifyUser(Request $request)
    {
        $check = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $required = $check == 'phone' ? 'required|string|starts_with:01|exists:users,phone' : 'required|email|exists:users,email';
        $validate = Validator::make($request->all(), [
            'phone' => $required,
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        $token = config('app.env') == 'production' ? Fa::token() : "123456";
        if ($request->isNotFilled('type')) {
            $type = $check == 'phone' ? 'sms' : 'email';
            $request->merge(['type' => $type]);
        }
        if (config('app.env') == 'production') {
            if ($request->type == 'sms') {
                $status = SmsGateway::send($request->phone, $token);
                if (@$status['code'] != 200) {
                    return response()->json([
                        'status_code' => 403,
                        'message' => $status['message'],
                    ], 403);
                }
            } else {
                Notification::route('mail', $request->phone)->notify(new AuthTokenNotification($token));
            }
        }
        PasswordResetToken::updateOrCreate([
            'email' => $request->phone,
        ], ['token' => $token,'created_at'=>now()]);
        return response()->json([
            'status_code' => 200,
            'token' => config('app.env') == 'local' ? $token : null,
        ], 200);
    }
    //TODO @fa 2025-03-20 user verification
    public function verifyToken(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'otp' => 'required|string|exists:password_reset_tokens,token',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        $token = PasswordResetToken::query()->where('token', $request->otp)
            ->whereNotNull('created_at')
            ->first();
        if (!$token) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Verification unsuccessful',
            ], 404);
        }
        $check = filter_var($token->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::query()->where($check, $request->phone)->first();
        $user->update([
            'email_verified_at' => now(),
        ]);
        $accessToken = $user->createToken('authToken')->plainTextToken;
        $token->delete();
        return response()->json([
            'status_code' => 200,
            'message' => 'User logged in successfully',
            'data' => [
                'token' => $accessToken,
                'user' => $user,
            ],
        ], 200);
    }
    //TODO @fa 2025-03-23 resendOtp
    public function resendOtp(Request $request)
    {
        $check = filter_var($request->phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $required = $check == 'phone' ? 'required|string|starts_with:01|exists:users,phone' : 'required|email|exists:users,email';
        $validate = Validator::make($request->all(), [
            'phone' => $required,
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Validation error',
                'errors' => $validate->errors(),
            ], 422);
        }
        if ($request->isNotFilled('type')) {
            $request->merge(['type' => $check == 'phone' ? 'sms' : 'email']);
        }
        $user = User::query()->where($check, $request->phone)->first();
        $data = $this->sendToken($user,$request->type);
        if ($data['status_code'] == 200) {
            return response()->json([
                'status_code' => 200,
                'message' => 'OTP sent successfully',
            ], 200);
        }else{
            return response()->json([
                'status_code' => $data['status_code'],
                'message' => $data['message'],
            ], $data['status_code']);
        }
    }
}
