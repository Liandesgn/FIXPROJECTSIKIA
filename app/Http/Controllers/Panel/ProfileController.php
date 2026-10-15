<?php

namespace App\Http\Controllers\Panel;

use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('panel.profile.edit', [
            'user' => $request->user()->load(['roles', 'wilayahKerja', 'bidanWilayah', 'kaderWilayah']),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill($data);

        if ($user->isOrangTua() && ($data['status_kehamilan'] ?? null) !== StatusKehamilanEnum::Hamil->value) {
            $user->hpl = null;
            $user->usia_kehamilan = null;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak sesuai.',
            ]);
        }

        $user->update(['password' => $request->validated('password')]);
        $user->tokens()->delete();

        return back()->with('success', 'Password berhasil diubah.');
    }
}
