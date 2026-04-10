<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'role_id' => $request->defaultRoleId(),
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::created([
            'user' => new UserResource($user->load('role')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return ApiResponse::unauthorized('Invalid credentials.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => new UserResource($user->load('role')),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User logged in successfully.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'User logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(
            new UserResource($request->user()->load('role')),
            'User profile fetched successfully.'
        );
    }
}
