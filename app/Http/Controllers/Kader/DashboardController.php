<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('wilayahKerja');

        $stats = [
            'posyandu' => 0,
            'pengukuran' => 0,
            'notifikasi' => 0,
        ];

        return view('kader.dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }
}
