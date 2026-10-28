<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WilayahKerjaResource;
use App\Models\WilayahKerja;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WilayahKerjaController extends Controller
{
    public function index(): AnonymousResourceCollection
{
    return WilayahKerjaResource::collection(
        WilayahKerja::with('bidan')
            ->withCount('posyandu')
            ->orderBy('nama_wilayah')
            ->get()
    );
}
}
