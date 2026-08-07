<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Customer\Requests\LoginRequest;
use App\Domain\Customer\Requests\RegisterRequest;
use App\Domain\Customer\Resources\UserResource;
use App\Domain\Customer\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'کاربر با موفقیت ثبت شد',
            'token'=>$token,
            'user'=>new UserResource($user)
            ], 201);
    }

    public function login(LoginRequest $request)
    {
        $request->authenticate();
        $user = User::where('email', $request->email)->first();
       
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'=>'ورود با موفقیت',
            'token'=>$token,
            'user'=>new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'با موفقیت از سیستم خارج شدید']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user'=>new UserResource($request->user())
        ]);
    }
}
