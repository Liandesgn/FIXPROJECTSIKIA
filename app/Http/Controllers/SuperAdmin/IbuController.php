<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IbuController extends Controller
{
    public function index(Request $request): View
    {
        $ibus = User::query()
            ->role(RoleEnum::Ibu->value)
            ->with('ibu.wilayahKerja')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhereHas('ibu', function ($i) use ($search) {
                            $i->where('nik', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('wilayah_kerja_id'), function ($query) use ($request) {
                $query->whereHas('ibu', function ($i) use ($request) {
                    $i->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id'));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.ibu.index', [
            'ibus' => $ibus,
            'wilayahs' => WilayahKerja::orderBy('kecamatan')
                ->orderBy('kelurahan')
                ->get(),
            'statuses' => UserStatusEnum::cases(),
            'readOnly' => true,
        ]);
    }
}
