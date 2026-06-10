<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function show(Request $request): JsonResponse
    {

        return response()->json([
            'data' => $request->user()
        ]);
    }
    public function register(RegisterUserRequest $request,UserService $userService): JsonResponse
    {
        $user = $userService->create($request->validated());

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('api')->plainTextToken
        ]);
    }

    public function login(LoginUserRequest $request,UserService $userService): JsonResponse|array
    {
        $loginSuccess = $userService->login($request->validated());

        if (!$loginSuccess) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return [
            'token' => Auth::user()->createToken('api')->plainTextToken
        ];
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'message' => 'logged out successfully'
        ]);
    }
}
