<?php

namespace App\Http\Controllers\Panel;

use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('panel.profile.edit', [
            'user' => $request->user()->load([
                'roles',
                'wilayahKerja',
                'bidanWilayah',
                'kaderWilayah',
                'ibu.wilayahKerja.bidan',
                'ibu.wilayahKerja.kader',
            ]),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Simpan data akun ke tabel users.
        $user->fill(collect($data)->only([
            'name',
            'email',
            'phone',
        ])->all());

        $user->save();

        // Simpan data pribadi ke tabel ibu.
        if ($user->isOrangTua()) {
            $ibuData = collect($data)->only([
                'nik',
                'tempat_lahir',
                'tanggal_lahir',
                'status_kehamilan',
                'hpl',
                'usia_kehamilan',
            ])->all();

            if (array_key_exists('status_kehamilan', $ibuData)
                && $ibuData['status_kehamilan'] !== StatusKehamilanEnum::Hamil->value) {
                $ibuData['hpl'] = null;
                $ibuData['usia_kehamilan'] = null;
            }

            if (! empty($ibuData)) {
                $user->ibu()->updateOrCreate(
                    ['user_id' => $user->id],
                    $ibuData
                );
            }
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}