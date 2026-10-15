<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (! $user->isActive()) {
            return response()->json([
                'message' => 'Akun Anda sedang nonaktif. Silakan hubungi admin.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
                'token' => $token,
                'token_type' => 'Bearer',
                'dashboard' => $user->primaryRole()?->dashboard(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles', 'wilayahKerja');

        return response()->json([
            'data' => (new UserResource($user))->resolve(),
        ]);
    }
}
