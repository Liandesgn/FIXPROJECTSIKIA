<?php

namespace App\Http\Controllers\Admin;


use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PosyanduRequest;
use App\Models\Kader;
use App\Models\Posyandu;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosyanduController extends Controller
{
    public function index(Request $request): View
    {
        $posyandus = Posyandu::query()
            ->with('wilayahKerja')
            ->when($request->filled('search'), fn ($q) => $q
                ->where('nama_posyandu', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('wilayah_kerja_id'), fn ($q) => $q
                ->where('wilayah_kerja_id', $request->integer('wilayah_kerja_id')))
            ->orderBy('nama_posyandu')
            ->get();

        return view('admin.posyandu.index', [
            'posyandus' => $posyandus,
            'wilayahs' => $this->wilayahs(),
            // jumlah kader per wilayah, untuk kolom "Kader"
            'kaderCount' => Kader::query()
                ->selectRaw('wilayah_kerja_id, count(*) as n')
                ->groupBy('wilayah_kerja_id')
                ->pluck('n', 'wilayah_kerja_id'),
        ]);
    }

    public function create(): View
{
    return view('admin.posyandu.create', [
        'wilayahs' => $this->wilayahs(),
        'kaders' => Kader::with('user')
            ->orderBy('id')
            ->get(),
    ]);
}

    public function store(PosyanduRequest $request): RedirectResponse
    {
        Posyandu::create($request->validated());

        return redirect()
            ->route('admin.posyandu.index')
            ->with('success', 'Data posyandu berhasil ditambahkan.');
    }

    public function edit(Posyandu $posyandu): View
{
    return view('admin.posyandu.edit', [
        'posyandu' => $posyandu,
        'wilayahs' => $this->wilayahs(),
        'kaders' => Kader::with('user')
            ->orderBy('id')
            ->get(),
    ]);
}

    public function update(PosyanduRequest $request, Posyandu $posyandu): RedirectResponse
    {
        $posyandu->update($request->validated());

        return redirect()
            ->route('admin.posyandu.index')
            ->with('success', 'Data posyandu berhasil diperbarui.');
    }

    public function destroy(Posyandu $posyandu): RedirectResponse
    {
        if ($posyandu->kegiatan()->exists()) {
            return back()->with('error', 'Data tidak dapat dihapus karena masih terkait data kegiatan.');
        }

        $posyandu->delete();

        return redirect()
            ->route('admin.posyandu.index')
            ->with('success', 'Data posyandu berhasil dihapus.');
    }

    private function wilayahs()
    {
        return WilayahKerja::query()->orderBy('kecamatan')->orderBy('desa')->get();
    }
}