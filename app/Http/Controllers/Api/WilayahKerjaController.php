<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\WilayahKerjaRequest;
use App\Models\WilayahKerja;
use App\Models\Kecamatan;
use App\Models\Puskesmas;
use App\Models\KelurahanDesa;
use App\Models\Rw;
use App\Models\Rt;
use Illuminate\Http\Request;

class WilayahKerjaController extends Controller
{
    /**
     * Menampilkan daftar wilayah kerja.
     */
    public function index(Request $request)
    {
        $wilayahs = WilayahKerja::query()
            ->with([
                'kecamatan',
                'puskesmas',
                'kelurahanDesa',
                'rw',
                'rt',
                'bidan',
            ])
            ->withCount('admins')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where('nama_wilayah', 'like', "%{$search}%")
                    ->orWhereHas('kecamatan', function ($q) use ($search) {
                        $q->where('nama_kecamatan', 'like', "%{$search}%");
                    })
                    ->orWhereHas('puskesmas', function ($q) use ($search) {
                        $q->where('nama_puskesmas', 'like', "%{$search}%");
                    })
                    ->orWhereHas('kelurahanDesa', function ($q) use ($search) {
                        $q->where('nama_kelurahan_desa', 'like', "%{$search}%");
                    });
            })
            ->orderByDesc('id')
            ->get();

        return view('superadmin.wilayah.index', compact('wilayahs'));
    }

    /**
     * Form tambah wilayah kerja.
     */
    public function create()
    {
        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('superadmin.wilayah.create', compact('kecamatans'));
    }

    /**
     * Simpan wilayah kerja baru.
     */
    public function store(WilayahKerjaRequest $request)
    {
        WilayahKerja::create($request->validated());

        return redirect()
            ->route('superadmin.wilayah.index')
            ->with('success', 'Wilayah kerja berhasil ditambahkan.');
    }

    /**
     * Form edit wilayah kerja.
     */
    public function edit(WilayahKerja $wilayah)
    {
        $wilayah->load([
            'kecamatan',
            'puskesmas',
            'kelurahanDesa',
            'rw',
            'rt',
        ]);

        $kecamatans = Kecamatan::orderBy('nama_kecamatan')->get();

        $puskesmas = Puskesmas::where(
            'kecamatan_id',
            $wilayah->kecamatan_id
        )->orderBy('nama_puskesmas')->get();

        $kelurahanDesas = KelurahanDesa::where(
            'puskesmas_id',
            $wilayah->puskesmas_id
        )->where(
            'kecamatan_id',
            $wilayah->kecamatan_id
        )->orderBy('nama_kelurahan_desa')->get();

        $rws = Rw::where(
            'kelurahan_desa_id',
            $wilayah->kelurahan_desa_id
        )->orderBy('nomor_rw')->get();

        $rts = Rt::where(
            'rw_id',
            $wilayah->rw_id
        )->orderBy('nomor_rt')->get();

        return view('superadmin.wilayah.edit', compact(
            'wilayah',
            'kecamatans',
            'puskesmas',
            'kelurahanDesas',
            'rws',
            'rts'
        ));
    }

    /**
     * Perbarui wilayah kerja.
     */
    public function update(WilayahKerjaRequest $request, WilayahKerja $wilayah)
    {
        $wilayah->update($request->validated());

        return redirect()
            ->route('superadmin.wilayah.index')
            ->with('success', 'Wilayah kerja berhasil diperbarui.');
    }

    /**
     * Hapus wilayah kerja jika belum digunakan.
     */
    public function destroy(WilayahKerja $wilayah)
    {
        $terpakai =
            $wilayah->users()->exists() ||
            $wilayah->admins()->exists() ||
            $wilayah->kegiatanPosyandu()->exists() ||
            $wilayah->ibuses()->exists() ||
            $wilayah->bidan()->exists() ||
            $wilayah->kader()->exists();

        if ($terpakai) {
            return redirect()
                ->route('superadmin.wilayah.index')
                ->with('error', 'Wilayah tidak dapat dihapus karena masih digunakan.');
        }

        $wilayah->delete();

        return redirect()
            ->route('superadmin.wilayah.index')
            ->with('success', 'Wilayah kerja berhasil dihapus.');
    }

    /**
     * Dropdown Puskesmas berdasarkan Kecamatan.
     */
    public function getPuskesmas(Request $request)
    {
        $request->validate([
            'kecamatan_id' => ['required', 'exists:kecamatan,id'],
        ]);

        $data = Puskesmas::where('kecamatan_id', $request->kecamatan_id)
            ->orderBy('nama_puskesmas')
            ->get(['id', 'nama_puskesmas']);

        return response()->json($data);
    }

    /**
     * Dropdown Kelurahan/Desa berdasarkan Puskesmas dan Kecamatan.
     */
    public function getKelurahanDesa(Request $request)
    {
        $request->validate([
            'kecamatan_id' => ['required', 'exists:kecamatan,id'],
            'puskesmas_id' => ['required', 'exists:puskesmas,id'],
        ]);

        $data = KelurahanDesa::where('kecamatan_id', $request->kecamatan_id)
            ->where('puskesmas_id', $request->puskesmas_id)
            ->orderBy('nama_kelurahan_desa')
            ->get(['id', 'nama_kelurahan_desa', 'jenis']);

        return response()->json($data);
    }

    /**
     * Dropdown RW berdasarkan Kelurahan/Desa.
     */
    public function getRw(Request $request)
    {
        $request->validate([
            'kelurahan_desa_id' => ['required', 'exists:kelurahan_desa,id'],
        ]);

        $data = Rw::where('kelurahan_desa_id', $request->kelurahan_desa_id)
            ->orderBy('nomor_rw')
            ->get(['id', 'nomor_rw']);

        return response()->json($data);
    }

    /**
     * Dropdown RT berdasarkan RW.
     */
    public function getRt(Request $request)
    {
        $request->validate([
            'rw_id' => ['required', 'exists:rw,id'],
        ]);

        $data = Rt::where('rw_id', $request->rw_id)
            ->orderBy('nomor_rt')
            ->get(['id', 'nomor_rt']);

        return response()->json($data);
    }
}
