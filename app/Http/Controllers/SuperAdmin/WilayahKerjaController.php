<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WilayahKerjaRequest;
use App\Models\WilayahKerja;
use App\Models\Kecamatan;
use App\Models\Puskesmas;
use App\Models\KelurahanDesa;
use App\Models\Rw;
use App\Models\Rt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WilayahKerjaController extends Controller
{
    public function index(Request $request): View
    {
        $wilayahs = WilayahKerja::query()
            ->with('bidan')
            ->withCount('admins')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->string('search') . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('nama_wilayah', 'like', $search)
                        ->orWhere('kelurahan', 'like', $search)
                        ->orWhere('kecamatan', 'like', $search);
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('superadmin.wilayah.index', compact('wilayahs'));
    }

    public function create(): View
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('superadmin.wilayah.create', compact('kecamatans'));
    }

    public function store(WilayahKerjaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $kelurahan = KelurahanDesa::findOrFail($data['kelurahan_desa_id']);
        $rw = Rw::findOrFail($data['rw_id']);
        $rt = Rt::findOrFail($data['rt_id']);

        $data['nama_wilayah'] = $kelurahan->nama_kelurahan_desa
            . ' RW ' . $rw->nomor_rw
            . ' RT ' . $rt->nomor_rt;

        $data['kabupaten'] = 'Gresik';

        if (empty($data['deskripsi'])) {
            $data['deskripsi'] = 'Wilayah kerja ' . $data['nama_wilayah']
                . ', Kabupaten Gresik.';
        }

        WilayahKerja::create($data);

        return redirect()
            ->route('superadmin.wilayah-kerja.index')
            ->with('success', 'Data wilayah berhasil ditambahkan.');
    }

    public function edit(WilayahKerja $wilayah): View
    {
        return view('superadmin.wilayah.edit', compact('wilayah'));
    }

    public function update(
        WilayahKerjaRequest $request,
        WilayahKerja $wilayah
    ): RedirectResponse {
        $data = $request->validated();

        // Ambil data kelurahan/desa berdasarkan ID
        $kelurahan = KelurahanDesa::findOrFail(
            $data['kelurahan_desa_id']
        );

        // Ambil data RW berdasarkan ID
        $rw = Rw::findOrFail($data['rw_id']);

        // Ambil data RT berdasarkan ID
        $rt = Rt::findOrFail($data['rt_id']);

        // Ambil data kecamatan berdasarkan ID
        $kecamatan = Kecamatan::findOrFail(
            $data['kecamatan_id']
        );

        // Bentuk nama wilayah secara otomatis
        $data['nama_wilayah'] = $kelurahan->nama_kelurahan_desa
            . ' RW ' . $rw->nomor_rw
            . ' RT ' . $rt->nomor_rt;

        // Tetapkan kabupaten
        $data['kabupaten'] = 'Gresik';

        // Bentuk deskripsi secara otomatis
        $data['deskripsi'] = 'Wilayah kerja ' . $data['nama_wilayah']
            . ' yang berada di Kecamatan ' . $kecamatan->nama_kecamatan
            . ', Kelurahan/Desa ' . $kelurahan->nama_kelurahan_desa
            . ', Kabupaten Gresik.';

        // Simpan perubahan
        $wilayah->update($data);

        return redirect()
            ->route('superadmin.wilayah-kerja.index')
            ->with('success', 'Data wilayah berhasil diperbarui.');
    }

    public function destroy(WilayahKerja $wilayah): RedirectResponse
    {
        if (
            $wilayah->users()->exists()
            || $wilayah->admins()->exists()
            || $wilayah->kegiatanPosyandu()->exists()
            || \App\Models\Ibu::where('wilayah_kerja_id', $wilayah->id)->exists()
            || \App\Models\Bidan::where('wilayah_kerja_id', $wilayah->id)->exists()
            || \App\Models\Kader::where('wilayah_kerja_id', $wilayah->id)->exists()
        ) {
            return back()->with(
                'error',
                'Data tidak dapat dihapus karena masih terkait data lain.'
            );
        }

        $wilayah->delete();

        return redirect()
            ->route('superadmin.wilayah-kerja.index')
            ->with('success', 'Data wilayah berhasil dihapus.');
    }

    // DROPDOWN PUSKESMAS BERDASARKAN KECAMATAN
    public function getPuskesmas(Request $request): JsonResponse
    {
        $request->validate([
            'kecamatan_id' => ['required', 'integer', 'exists:kecamatan,id'],
        ]);

        return response()->json(
            Puskesmas::where('kecamatan_id', $request->kecamatan_id)
                ->orderBy('nama_puskesmas')
                ->get(['id', 'nama_puskesmas'])
        );
    }

    // DROPDOWN KELURAHAN/DESA BERDASARKAN PUSKESMAS
    public function getKelurahanDesa(Request $request): JsonResponse
    {
        $request->validate([
            'puskesmas_id' => ['required', 'integer', 'exists:puskesmas,id'],
        ]);

        return response()->json(
            KelurahanDesa::where('puskesmas_id', $request->puskesmas_id)
                ->orderBy('nama_kelurahan_desa')
                ->get(['id', 'nama_kelurahan_desa'])
        );
    }

    // DROPDOWN RW BERDASARKAN KELURAHAN/DESA
    public function getRw(Request $request): JsonResponse
    {
        $request->validate([
            'kelurahan_desa_id' => [
                'required',
                'integer',
                'exists:kelurahan_desa,id',
            ],
        ]);

        return response()->json(
            Rw::where('kelurahan_desa_id', $request->kelurahan_desa_id)
                ->orderBy('nomor_rw')
                ->get(['id', 'nomor_rw'])
        );
    }

    // DROPDOWN RT BERDASARKAN RW
    public function getRt(Request $request): JsonResponse
    {
        $request->validate([
            'rw_id' => ['required', 'integer', 'exists:rw,id'],
        ]);

        return response()->json(
            Rt::where('rw_id', $request->rw_id)
                ->orderBy('nomor_rt')
                ->get(['id', 'nomor_rt'])
        );
    }
}
