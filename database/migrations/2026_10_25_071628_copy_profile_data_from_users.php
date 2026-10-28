<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['ibu', 'bidan', 'kader'] as $role) {
            $roleId = DB::table('roles')->where('name', $role)->value('id');
            if (! $roleId) {
                continue;
            }

            // id semua user yang memegang role ini (data dari Spatie)
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            foreach (DB::table('users')->whereIn('id', $userIds)->get() as $u) {
                $data = [
                    'wilayah_kerja_id' => $u->wilayah_kerja_id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if ($role === 'ibu') {
                    $data += [
                        'nik' => ($u->nik ?? null) ?: null,
                        'tempat_lahir' => $u->tempat_lahir ?? null,
                        'tanggal_lahir' => $u->tanggal_lahir ?? null,
                        'status_kehamilan' => $u->status_kehamilan ?? null,
                        'usia_kehamilan' => $u->usia_kehamilan ?? null,
                        'hpl' => $u->hpl ?? null,
                    ];
                }

                // updateOrInsert: aman dijalankan ulang, tidak membuat data ganda
                DB::table($role)->updateOrInsert(['user_id' => $u->id], $data);
            }
        }
    }

    public function down(): void
    {
        // Sengaja kosong: tabel dihapus oleh migration pembuatnya.
    }
};