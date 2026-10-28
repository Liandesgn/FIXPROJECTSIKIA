<?php

namespace App\Http\Controllers\OrangTua;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('ibu.wilayahKerja');

        $wilayahId = $user->ibu?->wilayah_kerja_id
            ?? $user->wilayah_kerja_id;

        $bidanWilayah = null;
        $kaderWilayah = null;

        if ($wilayahId) {
            $wilayah = WilayahKerja::find($wilayahId);

            if ($wilayah) {
                $bidanWilayah = $wilayah->users()
                    ->whereHas('roles', function ($query) {
                        $query->where('name', RoleEnum::Bidan->value);
                    })
                    ->first();

                $kaderWilayah = $wilayah->users()
                    ->whereHas('roles', function ($query) {
                        $query->where('name', RoleEnum::Kader->value);
                    })
                    ->first();
            }
        }

        return view('orang-tua.dashboard', [
            'user' => $user,
            'bidanWilayah' => $bidanWilayah,
            'kaderWilayah' => $kaderWilayah,
        ]);
    }
}
