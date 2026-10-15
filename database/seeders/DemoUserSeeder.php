<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan akun demo lama yang tidak lagi memiliki wilayah kerja.
        User::whereIn('email', ['bidan@sikia.test', 'kader@sikia.test'])->delete();

        $wilayahs = WilayahKerja::query()->orderBy('kecamatan')->orderBy('desa')->get();

        foreach ($wilayahs as $wilayah) {
            $slug = Str::slug($wilayah->desa.' '.$wilayah->kecamatan);

            // Setiap desa memiliki 1 bidan dan 1 kader yang bertugas.
            $this->createUser(
                name: 'Bidan '.$wilayah->desa,
                email: 'bidan.'.$slug.'@sikia.test',
                role: RoleEnum::Bidan,
                wilayah: $wilayah,
            );

            $this->createUser(
                name: 'Kader '.$wilayah->desa,
                email: 'kader.'.$slug.'@sikia.test',
                role: RoleEnum::Kader,
                wilayah: $wilayah,
            );
        }

        // Orang tua demo pada desa pertama.
        $wilayah = $wilayahs->first();

        $orangTua = $this->createUser(
            name: 'Orang Tua Demo',
            email: 'orangtua@sikia.test',
            role: RoleEnum::OrangTua,
            wilayah: $wilayah,
        );

        $orangTua->forceFill([
            'nik' => '3525010101900001',
            'nama_ibu' => 'Siti Aminah',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1990-01-01',
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(3)->toDateString(),
            'usia_kehamilan' => 24,
        ])->save();
    }

    private function createUser(string $name, string $email, RoleEnum $role, ?WilayahKerja $wilayah): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'phone' => '081000000000',
                'status' => UserStatusEnum::Active->value,
                'wilayah_kerja_id' => $wilayah?->id,
            ],
        );

        if (! $user->hasRole($role->value)) {
            $user->syncRoles([$role->value]);
        }

        return $user;
    }
}
