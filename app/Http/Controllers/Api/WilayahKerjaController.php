<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WilayahKerjaResource;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WilayahKerjaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $wilayahId = $request->user()->isAdmin()
            ? $request->user()->loadMissing('admin')->managedWilayahId()
            : null;

        return WilayahKerjaResource::collection(
            WilayahKerja::with(['admins.user', 'bidan', 'kaders'])
                ->when($request->user()->isAdmin(), fn ($query) => $query
                    ->whereKey($wilayahId))
                ->when($request->user()->isAdmin() && ! $wilayahId, fn ($query) => $query
                    ->whereRaw('1 = 0'))
                ->orderBy('nama_wilayah')
                ->get()
        );
    }
}
