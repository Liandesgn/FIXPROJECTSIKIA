<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WilayahKerjaRequest;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WilayahKerjaController extends Controller
{

     public function index(Request $request): View
    {
        $wilayahs = WilayahKerja::query()
            ->with('bidan')
            ->withCount('posyandu')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search').'%';

                $query->where(function ($q) use ($search) {
                    $q->where('nama_wilayah', 'like', $search)
                        ->orWhere('desa', 'like', $search)
                        ->orWhere('kecamatan', 'like', $search);
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('admin.wilayah.index', compact('wilayahs'));
    }


    public function create(): View
    {
        return view('admin.wilayah.create');
    }

    public function store(WilayahKerjaRequest $request): RedirectResponse
    {
        WilayahKerja::create(
            $request->validated() + ['kabupaten' => 'Gresik']
        );

        return redirect()
            ->route('admin.wilayah-kerja.index')
            ->with('success', 'Data wilayah berhasil ditambahkan.');
    }

    public function edit(WilayahKerja $wilayah): View
    {
        return view('admin.wilayah.edit', compact('wilayah'));
    }

    public function update(
        WilayahKerjaRequest $request,
        WilayahKerja $wilayah
    ): RedirectResponse {
        $wilayah->update($request->validated());

        return redirect()
            ->route('admin.wilayah-kerja.index')
            ->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(WilayahKerja $wilayah): RedirectResponse
    {
        if (
            $wilayah->users()->exists()
            || $wilayah->posyandu()->exists()
            || \App\Models\Ibu::where(
                'wilayah_kerja_id',
                $wilayah->id
            )->exists()
            || \App\Models\Bidan::where(
                'wilayah_kerja_id',
                $wilayah->id
            )->exists()
            || \App\Models\Kader::where(
                'wilayah_kerja_id',
                $wilayah->id
            )->exists()
        ) {
            return back()->with(
                'error',
                'Data tidak dapat dihapus karena masih terkait data lain.'
            );
        }

        $wilayah->delete();

        return redirect()
            ->route('admin.wilayah-kerja.index')
            ->with('success', 'Data wilayah berhasil dihapus.');
    }
}