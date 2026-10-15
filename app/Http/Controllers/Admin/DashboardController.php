<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
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
            'orang_tua' => User::role(RoleEnum::OrangTua->value)->count(),
            'wilayah' => WilayahKerja::count(),
        ];

        $latestUsers = User::with(['roles', 'wilayahKerja'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestUsers'));
    }
}
