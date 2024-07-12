<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        User::create($request->all());

        return response()->json(['message' => 'Registro OK'], 200);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }

        return (new UserResource(Auth::user()))
            ->additional(['token' => Auth::user()->createToken('access_token')->plainTextToken]);
    }

    public function user(): UserResource
    {
        return new UserResource(Auth::user());
    }

    public function logout()
    {
        if (Auth::user()->tokens()->delete()) {
            return response()->json(['message' => 'User logged out!'], 200);
        }
    }
}
