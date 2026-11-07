<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\KegiatanPosyandu;
use App\Models\PemeriksaanIbu;
use App\Models\PemeriksaanPosyandu;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Statistik keseluruhan
        $stats = [
            'admin' => User::role(RoleEnum::Admin->value)->count(),

            'bidan' => User::role(RoleEnum::Bidan->value)->count(),

            'kader' => User::role(RoleEnum::Kader->value)->count(),

            'ibu' => User::role(RoleEnum::Ibu->value)->count(),

            'wilayah' => WilayahKerja::count(),

            'anak' => Anak::count(),

            'kegiatan' => KegiatanPosyandu::count(),
        ];

        // Perbandingan data setiap wilayah
        $perbandinganWilayah = WilayahKerja::query()
            ->orderBy('nama_wilayah')
            ->get()
            ->map(function ($wilayah) {

                // Jumlah ibu hamil di wilayah
                $ibuHamil = Ibu::where(
                    'wilayah_kerja_id',
                    $wilayah->id
                )
                    ->where(
                        'status_kehamilan',
                        StatusKehamilanEnum::Hamil->value
                    )
                    ->count();

                // Jumlah anak di wilayah
                $anak = Anak::whereHas('ibu', function ($query) use ($wilayah) {
                    $query->where(
                        'wilayah_kerja_id',
                        $wilayah->id
                    );
                })->count();

                // Jumlah kegiatan Posyandu di wilayah
                $kegiatan = KegiatanPosyandu::where(
                    'id_wilayah',
                    $wilayah->id
                )->count();

                // Ibu dengan pemeriksaan terakhir perlu perhatian
                $perhatianIbu = PemeriksaanIbu::query()
                    ->terakhirPerIbu()
                    ->where(
                        'status_kesehatan',
                        PemeriksaanIbu::PERLU_PERHATIAN
                    )
                    ->whereHas('ibu', function ($query) use ($wilayah) {
                        $query->where(
                            'wilayah_kerja_id',
                            $wilayah->id
                        );
                    })
                    ->distinct()
                    ->count('ibu_id');

                // Anak dengan pemeriksaan terakhir perlu perhatian
                $perhatianAnak = PemeriksaanPosyandu::query()
                    ->terakhirPerAnak()
                    ->where(
                        'status_kesehatan',
                        PemeriksaanPosyandu::PERLU_PERHATIAN
                    )
                    ->whereHas('anak.ibu', function ($query) use ($wilayah) {
                        $query->where(
                            'wilayah_kerja_id',
                            $wilayah->id
                        );
                    })
                    ->distinct()
                    ->count('anak_id');

                // Gabungan ibu dan anak yang perlu perhatian
                $wilayah->ibu_hamil_count = $ibuHamil;
                $wilayah->anak_count = $anak;
                $wilayah->kegiatan_count = $kegiatan;
                $wilayah->perlu_perhatian_count = $perhatianIbu + $perhatianAnak;

                return $wilayah;
            });

        return view('superadmin.dashboard', compact(
            'stats',
            'perbandinganWilayah'
        ));
    }
}
