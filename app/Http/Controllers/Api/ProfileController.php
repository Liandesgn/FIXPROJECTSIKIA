<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles', 'wilayahKerja');

        return response()->json([
            'data' => (new UserResource($user))->resolve(),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->fill($request->validated());
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => (new UserResource($user->load('roles', 'wilayahKerja')))->resolve(),
        ]);
    }

    public function updatePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            return response()->json([
                'message' => 'Password saat ini tidak sesuai.',
                'errors' => [
                    'current_password' => ['Password saat ini tidak sesuai.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => $request->validated('password'),
        ]);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password berhasil diubah. Silakan login kembali.',
        ]);
    }
}
