<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('panel.profile.edit', [
            'user' => $request->user()->load([
                'roles',
                'admin.wilayahKerja',
                'bidan.wilayahKerja',
                'kader.wilayahKerja',
                'ibu.wilayahKerja.bidan',
                'ibu.wilayahKerja.kader',
            ]),
        ]);
    }
}
