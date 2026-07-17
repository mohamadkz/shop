<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
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
            'message' => 'User registered successfully',
            'token'=>$token,
            'user'=>new UserResource($user)
            ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email','password')))
        {
            return response()->json([
                'message'=>'Credentials are incorrect'
            ],401);
        }
        $user = User::where('email', $request->email)->first();
        // ایجاد توکن
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'=>'Login Successfully',
            'token'=>$token,
            'user'=>new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        // حذف توکنی که برای این دستگاه صادر شده
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user'=>new UserResource($request->user())
        ]);
    }
}
