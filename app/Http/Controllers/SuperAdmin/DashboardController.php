<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'admin' => User::role(RoleEnum::Admin->value)->count(),
            'bidan' => User::role(RoleEnum::Bidan->value)->count(),
            'kader' => User::role(RoleEnum::Kader->value)->count(),
            'ibu' => User::role(RoleEnum::Ibu->value)->count(),
            'wilayah' => WilayahKerja::count(),
            'admin_tanpa_wilayah' => Admin::whereNull('id_wilayah')->count(),
        ];

        return view('superadmin.dashboard', compact('stats'));
    }
}
