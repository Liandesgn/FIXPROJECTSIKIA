<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\KegiatanPosyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('admin');
        $wilayahId = $user->managedWilayahId();

        $stats = [
            'bidan' => 0,
            'kader' => 0,
            'ibu_hamil' => 0,
            'anak' => 0,
            'wilayah' => $wilayahId ? 1 : 0,
            'jadwal' => 0,
        ];

        $latestUsers = collect();

        if ($wilayahId) {
            $stats['bidan'] = User::role(RoleEnum::Bidan->value)
                ->whereHas('bidan', fn ($q) => $q->where('wilayah_kerja_id', $wilayahId))
                ->count();

            $stats['kader'] = User::role(RoleEnum::Kader->value)
                ->whereHas('kader', fn ($q) => $q->where('wilayah_kerja_id', $wilayahId))
                ->count();

            $stats['ibu_hamil'] = Ibu::where('wilayah_kerja_id', $wilayahId)
                ->where('status_kehamilan', StatusKehamilanEnum::Hamil->value)
                ->count();

            $stats['anak'] = Anak::whereHas(
                'ibu',
                fn ($q) => $q->where('wilayah_kerja_id', $wilayahId)
            )->count();

            $stats['jadwal'] = KegiatanPosyandu::where('id_wilayah', $wilayahId)
                ->whereDate('tanggal', '>=', today())
                ->count();

            $latestUsers = User::with(['roles', 'wilayahKerja', 'ibu.wilayahKerja'])
                ->where(function ($query) use ($wilayahId) {
                    $query->whereHas('bidan', fn ($q) => $q->where('wilayah_kerja_id', $wilayahId))
                        ->orWhereHas('kader', fn ($q) => $q->where('wilayah_kerja_id', $wilayahId))
                        ->orWhereHas('ibu', fn ($q) => $q->where('wilayah_kerja_id', $wilayahId));
                })
                ->latest()
                ->take(5)
                ->get();
        }

        return view('admin.dashboard', compact('stats', 'latestUsers'));
    }
}