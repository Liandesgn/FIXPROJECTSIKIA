<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['wilayahKerja', 'bidanWilayah', 'kaderWilayah']);

        return view('orang-tua.dashboard', [
            'user' => $user,
        ]);
    }
}
