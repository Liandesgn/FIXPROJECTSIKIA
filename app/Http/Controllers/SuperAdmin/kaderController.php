<?php

namespace App\Http\Controllers\SuperAdmin;
use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaderController extends Controller
{
    public function index(Request $request): View
    {
        $kaders = User::query()
            ->role(RoleEnum::Kader->value)
            ->with('kader.wilayahKerja')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($request->filled('wilayah_kerja_id'), function ($query) use ($request) {
                $query->whereHas('kader', function ($k) use ($request) {
                    $k->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id'));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kader.index', [
            'kaders' => $kaders,
            'wilayahs' => WilayahKerja::orderBy('kecamatan')
                ->orderBy('kelurahan')
                ->get(),
            'readOnly' => true,
        ]);
    }
}
