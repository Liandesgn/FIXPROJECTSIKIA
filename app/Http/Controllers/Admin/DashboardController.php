<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Bidan;
use App\Models\Ibu;
use App\Models\Kader;
use App\Models\KegiatanPosyandu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load([
            'admin',
        ]);

        // Ambil wilayah yang dikelola Admin.
        $wilayahId = $user->managedWilayahId();

        // Nilai awal jika Admin belum memiliki wilayah.
        $stats = [
            'bidan' => 0,
            'kader' => 0,
            'ibu_hamil' => 0,
            'anak' => 0,
            'wilayah' => $wilayahId ? 1 : 0,
            'jadwal' => 0,
        ];

        // Jadwal Posyandu mendatang.
        $jadwalPosyandu = collect();

        // Hitung data hanya jika Admin memiliki wilayah.
        if ($wilayahId) {

            // Jumlah Bidan pada wilayah Admin.
            $stats['bidan'] = Bidan::query()
                ->where('wilayah_kerja_id', $wilayahId)
                ->count();

            // Jumlah Kader pada wilayah Admin.
            $stats['kader'] = Kader::query()
                ->where('wilayah_kerja_id', $wilayahId)
                ->count();

            // Jumlah Ibu dengan status kehamilan Hamil.
            $stats['ibu_hamil'] = Ibu::query()
                ->where('wilayah_kerja_id', $wilayahId)
                ->where(
                    'status_kehamilan',
                    StatusKehamilanEnum::Hamil->value
                )
                ->count();

            // Jumlah Anak pada wilayah Admin.
            $stats['anak'] = Anak::query()
                ->visibleTo($user)
                ->count();

            // Jumlah seluruh kegiatan Posyandu pada wilayah Admin.
            $stats['jadwal'] = KegiatanPosyandu::query()
                ->diWilayah($wilayahId)
                ->count();

            // Lima jadwal Posyandu terdekat pada wilayah Admin.
            $jadwalPosyandu = KegiatanPosyandu::query()
                ->with('wilayah')
                ->diWilayah($wilayahId)
                ->mendatang()
                ->limit(5)
                ->get();
        }

        return view('admin.dashboard', compact(
            'stats',
            'jadwalPosyandu'
        ));
    }
}