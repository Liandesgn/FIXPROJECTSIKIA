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

       
        $jadwalPosyandu = kegiatanPosyandu::with('wilayah')
        ->mendatang()
        ->limit(5)
        ->get();

        return view('admin.dashboard', compact('stats', 'jadwalPosyandu'));
    }
}
