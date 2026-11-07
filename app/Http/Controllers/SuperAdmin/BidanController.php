<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BidanController extends Controller
{
    public function index(Request $request): View
    {
        $bidans = User::query()
            ->role(RoleEnum::Bidan->value)
            ->with('bidan.wilayahKerja')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($request->filled('wilayah_kerja_id'), function ($query) use ($request) {
                $query->whereHas('bidan', function ($b) use ($request) {
                    $b->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id'));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bidan.index', [
            'bidans' => $bidans,
            'wilayahs' => WilayahKerja::orderBy('kecamatan')
                ->orderBy('kelurahan')
                ->get(),
            'readOnly' => true,
        ]);
    }
}
