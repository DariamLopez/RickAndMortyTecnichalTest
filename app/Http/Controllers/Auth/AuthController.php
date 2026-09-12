<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password'))
        ]);

        [, $plainTextToken] = $user->issueToken('api-client');

        return response()->json([
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'token' => $plainTextToken
            ]
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        [, $plainTextToken] = $user->issueToken('api-client');

        return response()->json([
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'token' => $plainTextToken
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $plainTextToken = $request->bearerToken();
        $request->user()?->tokens()->where('token', hash('sha256', $plainTextToken))->delete();
        return response()->json(['data' => ['message' => 'Logged out successfully']]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->only(['id', 'name', 'email'])
        ]);
    }
}
