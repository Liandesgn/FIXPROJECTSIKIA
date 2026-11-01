<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardKader;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $dashboard = new DashboardKader($request->user());

        abort_unless($dashboard->cekRole(), 403, 'Akses ditolak.');

        return view('kader.dashboard', [
            'user' => $request->user(),
            'wilayah' => $dashboard->wilayah(),
            'ringkasan' => $dashboard->ambilRingkasan(),
            'jadwal' => $dashboard->jadwal(),
            'notifikasi' => $dashboard->ambilNotifikasiTerbaru(),
        ]);
    }
}