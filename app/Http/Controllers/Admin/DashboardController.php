<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Http\Controllers\Controller;
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
            'ibu_hamil' => User::role(RoleEnum::OrangTua->value)
                ->where('status_kehamilan', StatusKehamilanEnum::Hamil->value)
                ->count(),
            'anak' => 0,
            'wilayah' => WilayahKerja::count(),
            'jadwal' => 0,
        ];

        $latestUsers = User::with(['roles', 'wilayahKerja'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestUsers'));
    }
}
