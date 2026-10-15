<?php

namespace App\Http\Controllers\Bidan;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('wilayahKerja');

        $stats = [
            'orang_tua' => User::role(RoleEnum::OrangTua->value)->count(),
            'pemeriksaan' => 0,
            'konsultasi' => 0,
            'jadwal' => 0,
        ];

        return view('bidan.dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
