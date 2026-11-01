<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardBidan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $dashboard = new DashboardBidan($request->user());

        abort_unless($dashboard->cekRole(), 403, 'Akses ditolak.');

        return view('bidan.dashboard', [
            'user' => $request->user(),
            'wilayah' => $dashboard->wilayah(),
            'ringkasan' => $dashboard->ambilRingkasan(),
            'jadwal' => $dashboard->jadwal(),
            'notifikasi' => $dashboard->ambilNotifikasiTerbaru(),
        ]);
    }
}