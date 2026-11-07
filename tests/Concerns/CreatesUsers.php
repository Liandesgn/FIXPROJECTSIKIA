<?php

namespace Tests\Concerns;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\WilayahKerja;

trait CreatesUsers
{
    protected function makeWilayah(array $attributes = []): WilayahKerja
    {
        return WilayahKerja::create(array_merge([
            'nama_wilayah' => 'Desa Sumberame, Kec. Wringinanom',
            'kabupaten' => 'Gresik',
            'kecamatan' => 'Wringinanom',
            'kelurahan' => 'Sumberame',
        ], $attributes));
    }

    protected function makeAdmin(?WilayahKerja $wilayah = null, array $attributes = []): User
    {
        $wilayah ??= $this->makeWilayah();

        $admin = User::factory()->create($attributes);
        $admin->assignRole(RoleEnum::Admin->value);
        $admin->admin()->create(['id_wilayah' => $wilayah->id]);

        return $admin->load('admin.wilayahKerja');
    }

    protected function makeUser(RoleEnum $role, ?WilayahKerja $wilayah = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role->value);

        if (in_array($role, [RoleEnum::Bidan, RoleEnum::Kader, RoleEnum::Ibu], true)) {
            $wilayah ??= $this->makeWilayah();

            match ($role) {
                RoleEnum::Bidan => $user->bidan()->create(['wilayah_kerja_id' => $wilayah->id]),
                RoleEnum::Kader => $user->kader()->create(['wilayah_kerja_id' => $wilayah->id]),
                RoleEnum::Ibu => $user->ibu()->create(['wilayah_kerja_id' => $wilayah->id]),
            };

            $user->forceFill(['wilayah_kerja_id' => $wilayah->id])->save();
        }

        return $user->load(['admin', 'bidan', 'kader', 'ibu']);
    }
}
