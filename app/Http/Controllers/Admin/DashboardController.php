<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Models\KegiatanPosyandu;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'bidan' => User::role(RoleEnum::Bidan->value)->count(),
            'kader' => User::role(RoleEnum::Kader->value)->count(),
            'ibu_hamil' => Ibu::where('status_kehamilan', StatusKehamilanEnum::Hamil->value)->count(),
            'anak' => Anak::count(),
            'wilayah' => WilayahKerja::count(),
            'jadwal' => KegiatanPosyandu::whereDate('tanggal', '>=', today())->count(),
        ];

        $latestUsers = User::with(['roles', 'wilayahKerja', 'ibu.wilayahKerja'])
            ->latest()
            ->take(5)
            ->get();

        $jadwalTerdekat = KegiatanPosyandu::with('posyandu')
        ->whereDate('tanggal', '>=', today())
        ->orderBy('tanggal')
        ->orderBy('waktu')
        ->take(3)
        ->get();

        return view('admin.dashboard', compact('stats', 'latestUsers', 'jadwalTerdekat'));
    }


}