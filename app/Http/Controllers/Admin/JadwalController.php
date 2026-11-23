<?php

namespace App\Http\Controllers\Admin;

use App\Models\KelurahanDesa;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Bidan;
use App\Models\Jadwal;
use App\Models\JadwalPemeriksaan;
use App\Models\JadwalPosyandu;
use App\Models\Kader;
use App\Models\KegiatanPosyandu;
use App\Models\Notifikasi;
use App\Models\Rw;
use App\Models\WilayahKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JadwalController extends Controller
{
    /**
     * Mengambil data Admin yang sedang login.
     */
    private function adminLogin(): Admin
    {
        return Admin::where('user_id', auth()->id())
            ->firstOrFail();
    }

  /**
 * Data pilihan form yang dibatasi berdasarkan Puskesmas Admin.
 */
private function dataForm(Admin $admin): array
{
    $puskesmasId = $admin->puskesmas_id;

    // Kelurahan/Desa dalam wilayah Puskesmas Admin.
    $kelurahanDesas = KelurahanDesa::where('puskesmas_id', $puskesmasId)
        ->orderBy('nama_kelurahan_desa')
        ->get();

    // RW beserta Kelurahan/Desa.
    $rws = Rw::whereHas('kelurahanDesa', function ($query) use ($puskesmasId) {
        $query->where('puskesmas_id', $puskesmasId);
    })
        ->with('kelurahanDesa')
        ->orderBy('nomor_rw')
        ->get();

    // Bidan beserta wilayah kerja dan Kelurahan/Desa.
    $bidans = Bidan::whereHas('wilayahKerja', function ($query) use ($puskesmasId) {
        $query->where('puskesmas_id', $puskesmasId);
    })
        ->with(['user', 'wilayahKerja.kelurahanDesa'])
        ->get();

    // Kader beserta wilayah kerja dan Kelurahan/Desa.
    $kaders = Kader::whereHas('wilayahKerja', function ($query) use ($puskesmasId) {
        $query->where('puskesmas_id', $puskesmasId);
    })
        ->with(['user', 'wilayahKerja.kelurahanDesa'])
        ->get();

    // Tetap disediakan untuk form Jadwal Pemeriksaan.
    $wilayahs = WilayahKerja::where('puskesmas_id', $puskesmasId)
        ->with('kelurahanDesa')
        ->get()
        ->unique('kelurahan_desa_id')
        ->values();

    return compact(
        'kelurahanDesas',
        'rws',
        'bidans',
        'kaders',
        'wilayahs'
    );
}

    /**
     * Memastikan RW berada di wilayah kerja Puskesmas Admin.
     */
    private function rwValid(int $rwId, Admin $admin): bool
    {
        return Rw::where('id', $rwId)
            ->whereHas('kelurahanDesa', function ($query) use ($admin) {
                $query->where('puskesmas_id', $admin->puskesmas_id);
            })
            ->exists();
    }

    /**
     * Memastikan Bidan berada di wilayah kerja Puskesmas Admin.
     */
    private function bidanValid(int $bidanId, Admin $admin): bool
    {
        return Bidan::where('id', $bidanId)
            ->whereHas('wilayahKerja', function ($query) use ($admin) {
                $query->where('puskesmas_id', $admin->puskesmas_id);
            })
            ->exists();
    }

    /**
     * Memastikan Kader berada di wilayah kerja Puskesmas Admin.
     */
    private function kaderValid(int $kaderId, Admin $admin): bool
    {
        return Kader::where('id', $kaderId)
            ->whereHas('wilayahKerja', function ($query) use ($admin) {
                $query->where('puskesmas_id', $admin->puskesmas_id);
            })
            ->exists();
    }

    /**
     * Memastikan wilayah berada di Puskesmas Admin.
     */
    private function wilayahValid(int $wilayahId, Admin $admin): bool
    {
        return WilayahKerja::where('id', $wilayahId)
            ->where('puskesmas_id', $admin->puskesmas_id)
            ->exists();
    }

    /**
     * Memastikan jadwal yang diakses milik Puskesmas Admin.
     */
    private function jadwalMilikAdmin(int $id, Admin $admin): Jadwal
    {
        $puskesmasId = $admin->puskesmas_id;

        $jadwal = Jadwal::where('id_jadwal', $id)
            ->where(function ($query) use ($puskesmasId) {
                $query->whereExists(function ($sub) use ($puskesmasId) {
                    $sub->selectRaw('1')
                        ->from('jadwal_posyandu as jp')
                        ->join('kegiatan_posyandu as kp', 'kp.id', '=', 'jp.id_kegiatan')
                        ->leftJoin('rw as r', 'r.id', '=', 'kp.rw_id')
                        ->leftJoin('kelurahan_desa as kd', 'kd.id', '=', 'r.kelurahan_desa_id')
                        ->leftJoin('wilayah_kerja as wk', 'wk.id', '=', 'kp.id_wilayah')
                        ->whereColumn('jp.id_jadwal', 'jadwal.id_jadwal')
                        ->where(function ($filter) use ($puskesmasId) {
                            $filter->where('kd.puskesmas_id', $puskesmasId)
                                ->orWhere('wk.puskesmas_id', $puskesmasId);
                        });
                })
                ->orWhereExists(function ($sub) use ($puskesmasId) {
                    $sub->selectRaw('1')
                        ->from('jadwal_pemeriksaan as jp')
                        ->join('wilayah_kerja as wk', 'wk.id', '=', 'jp.id_wilayah_kerja')
                        ->whereColumn('jp.id_jadwal', 'jadwal.id_jadwal')
                        ->where('wk.puskesmas_id', $puskesmasId);
                });
            })
            ->firstOrFail();

        return $jadwal;
    }

    /**
     * Daftar jadwal.
     */
    public function index()
    {
        $admin = $this->adminLogin();

        $puskesmasId = $admin->puskesmas_id;

        $jadwals = Jadwal::where(function ($query) use ($puskesmasId) {
            $query->whereExists(function ($sub) use ($puskesmasId) {
                $sub->selectRaw('1')
                    ->from('jadwal_posyandu as jp')
                    ->join('kegiatan_posyandu as kp', 'kp.id', '=', 'jp.id_kegiatan')
                    ->leftJoin('rw as r', 'r.id', '=', 'kp.rw_id')
                    ->leftJoin('kelurahan_desa as kd', 'kd.id', '=', 'r.kelurahan_desa_id')
                    ->leftJoin('wilayah_kerja as wk', 'wk.id', '=', 'kp.id_wilayah')
                    ->whereColumn('jp.id_jadwal', 'jadwal.id_jadwal')
                    ->where(function ($filter) use ($puskesmasId) {
                        $filter->where('kd.puskesmas_id', $puskesmasId)
                            ->orWhere('wk.puskesmas_id', $puskesmasId);
                    });
            })
            ->orWhereExists(function ($sub) use ($puskesmasId) {
                $sub->selectRaw('1')
                    ->from('jadwal_pemeriksaan as jp')
                    ->join('wilayah_kerja as wk', 'wk.id', '=', 'jp.id_wilayah_kerja')
                    ->whereColumn('jp.id_jadwal', 'jadwal.id_jadwal')
                    ->where('wk.puskesmas_id', $puskesmasId);
            });
        })
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->get();

        foreach ($jadwals as $jadwal) {
            $posyandu = JadwalPosyandu::where('id_jadwal', $jadwal->id_jadwal)->first();

            if ($posyandu) {
                $kegiatan = KegiatanPosyandu::find($posyandu->id_kegiatan);

                $jadwal->setAttribute('tipe_jadwal', 'posyandu');
                $jadwal->setAttribute('kegiatan', $kegiatan);
                $jadwal->setAttribute(
                    'rw',
                    $kegiatan?->rw_id ? Rw::with('kelurahanDesa')->find($kegiatan->rw_id) : null
                );
                $jadwal->setAttribute('bidan', Bidan::with('user')->find($posyandu->id_bidan));
                $jadwal->setAttribute('kader', Kader::with('user')->find($posyandu->id_kader));
            } else {
                $pemeriksaan = JadwalPemeriksaan::where('id_jadwal', $jadwal->id_jadwal)->first();

                $jadwal->setAttribute('tipe_jadwal', 'pemeriksaan');
                $jadwal->setAttribute('pemeriksaan', $pemeriksaan);
                $jadwal->setAttribute(
                    'wilayah',
                    $pemeriksaan
                        ? WilayahKerja::with('kelurahanDesa')->find($pemeriksaan->id_wilayah_kerja)
                        : null
                );
                $jadwal->setAttribute(
                    'bidan',
                    $pemeriksaan ? Bidan::with('user')->find($pemeriksaan->id_bidan) : null
                );
            }
        }

        return view('admin.jadwal.index', compact('jadwals'));
    }

    /**
     * Form tambah jadwal.
     */
    public function create()
    {
        $admin = $this->adminLogin();

        return view('admin.jadwal.create', $this->dataForm($admin));
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(Request $request)
    {
        $admin = $this->adminLogin();

        $data = $request->validate([
            'tipe_jadwal' => ['required', Rule::in(['posyandu', 'pemeriksaan'])],
            'jenis_kegiatan' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'waktu' => ['required'],
            'lokasi' => ['required', 'string', 'max:255'],

            'rw_id' => ['required_if:tipe_jadwal,posyandu', 'nullable', 'integer'],
            'id_bidan' => ['required', 'integer'],
            'id_kader' => ['required_if:tipe_jadwal,posyandu', 'nullable', 'integer'],

            'id_wilayah_kerja' => ['required_if:tipe_jadwal,pemeriksaan', 'nullable', 'integer'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (!$this->bidanValid((int) $data['id_bidan'], $admin)) {
            return back()->withInput()->withErrors([
                'id_bidan' => 'Bidan tidak berada dalam wilayah Puskesmas Anda.',
            ]);
        }

        if ($data['tipe_jadwal'] === 'posyandu') {
            if (!$this->rwValid((int) $data['rw_id'], $admin)) {
                return back()->withInput()->withErrors([
                    'rw_id' => 'RW tidak berada dalam wilayah Puskesmas Anda.',
                ]);
            }

            if (!$this->kaderValid((int) $data['id_kader'], $admin)) {
                return back()->withInput()->withErrors([
                    'id_kader' => 'Kader tidak berada dalam wilayah Puskesmas Anda.',
                ]);
            }
        } else {
            if (!$this->wilayahValid((int) $data['id_wilayah_kerja'], $admin)) {
                return back()->withInput()->withErrors([
                    'id_wilayah_kerja' => 'Wilayah tidak berada dalam Puskesmas Anda.',
                ]);
            }
        }

        DB::transaction(function () use ($data) {
            $jadwal = Jadwal::create([
                'jenis_kegiatan' => $data['jenis_kegiatan'],
                'tanggal' => $data['tanggal'],
                'waktu' => $data['waktu'],
                'lokasi' => $data['lokasi'],
            ]);

            if ($data['tipe_jadwal'] === 'posyandu') {
                $kegiatan = KegiatanPosyandu::create([
                    'id_wilayah' => null,
                    'rw_id' => $data['rw_id'],
                    'jenis_kegiatan' => $data['jenis_kegiatan'],
                    'tanggal' => $data['tanggal'],
                    'waktu' => $data['waktu'],
                    'lokasi' => $data['lokasi'],
                ]);

                JadwalPosyandu::create([
                    'id_jadwal' => $jadwal->id_jadwal,
                    'id_kegiatan' => $kegiatan->id,
                    'id_bidan' => $data['id_bidan'],
                    'id_kader' => $data['id_kader'],
                ]);
            } else {
                JadwalPemeriksaan::create([
                    'id_jadwal' => $jadwal->id_jadwal,
                    'id_wilayah_kerja' => $data['id_wilayah_kerja'],
                    'id_bidan' => $data['id_bidan'],
                    'catatan' => $data['catatan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('admin.jadwal.index', [
                'tab' => $request->tipe_jadwal
            ])
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Form edit jadwal.
     */
    public function edit($id)
    {
        $admin = $this->adminLogin();

        $jadwal = $this->jadwalMilikAdmin($id, $admin);

        $posyandu = JadwalPosyandu::where('id_jadwal', $jadwal->id_jadwal)->first();
        $pemeriksaan = JadwalPemeriksaan::where('id_jadwal', $jadwal->id_jadwal)->first();

        $kegiatan = $posyandu
            ? KegiatanPosyandu::find($posyandu->id_kegiatan)
            : null;

        $detail = [
            'tipe_jadwal' => $posyandu ? 'posyandu' : 'pemeriksaan',
            'rw_id' => $kegiatan?->rw_id,
            'id_bidan' => $posyandu?->id_bidan ?? $pemeriksaan?->id_bidan,
            'id_kader' => $posyandu?->id_kader,
            'id_wilayah_kerja' => $pemeriksaan?->id_wilayah_kerja,
            'catatan' => $pemeriksaan?->catatan,
        ];

        return view('admin.jadwal.edit', array_merge(
            compact('jadwal', 'detail'),
            $this->dataForm($admin)
        ));
    }

    /**
     * Perbarui jadwal.
     */
    public function update(Request $request, $id)
    {
        $admin = $this->adminLogin();

        $jadwal = $this->jadwalMilikAdmin($id, $admin);

        $posyandu = JadwalPosyandu::where('id_jadwal', $jadwal->id_jadwal)->first();
        $pemeriksaan = JadwalPemeriksaan::where('id_jadwal', $jadwal->id_jadwal)->first();

        $tipeJadwal = $posyandu ? 'posyandu' : 'pemeriksaan';

        $data = $request->validate([
            'jenis_kegiatan' => ['required', 'string', 'max:100'],
            'tanggal' => ['required', 'date'],
            'waktu' => ['required'],
            'lokasi' => ['required', 'string', 'max:255'],

            'rw_id' => ['required_if:tipe_jadwal,posyandu', 'nullable', 'integer'],
            'id_bidan' => ['required', 'integer'],
            'id_kader' => ['required_if:tipe_jadwal,posyandu', 'nullable', 'integer'],

            'id_wilayah_kerja' => ['required_if:tipe_jadwal,pemeriksaan', 'nullable', 'integer'],
            'catatan' => ['nullable', 'string'],
        ]);

        if (!$this->bidanValid((int) $data['id_bidan'], $admin)) {
            return back()->withInput()->withErrors([
                'id_bidan' => 'Bidan tidak berada dalam wilayah Puskesmas Anda.',
            ]);
        }

        if ($tipeJadwal === 'posyandu') {
            if (!$this->rwValid((int) $data['rw_id'], $admin)) {
                return back()->withInput()->withErrors([
                    'rw_id' => 'RW tidak berada dalam wilayah Puskesmas Anda.',
                ]);
            }

            if (!$this->kaderValid((int) $data['id_kader'], $admin)) {
                return back()->withInput()->withErrors([
                    'id_kader' => 'Kader tidak berada dalam wilayah Puskesmas Anda.',
                ]);
            }
        } else {
            if (!$this->wilayahValid((int) $data['id_wilayah_kerja'], $admin)) {
                return back()->withInput()->withErrors([
                    'id_wilayah_kerja' => 'Wilayah tidak berada dalam Puskesmas Anda.',
                ]);
            }
        }

        DB::transaction(function () use ($data, $jadwal, $posyandu, $pemeriksaan) {
            $jadwal->update([
                'jenis_kegiatan' => $data['jenis_kegiatan'],
                'tanggal' => $data['tanggal'],
                'waktu' => $data['waktu'],
                'lokasi' => $data['lokasi'],
            ]);

            if ($posyandu) {
                $kegiatan = KegiatanPosyandu::findOrFail($posyandu->id_kegiatan);

                $kegiatan->update([
                    'id_wilayah' => null,
                    'rw_id' => $data['rw_id'],
                    'jenis_kegiatan' => $data['jenis_kegiatan'],
                    'tanggal' => $data['tanggal'],
                    'waktu' => $data['waktu'],
                    'lokasi' => $data['lokasi'],
                ]);

                $posyandu->update([
                    'id_bidan' => $data['id_bidan'],
                    'id_kader' => $data['id_kader'],
                ]);
            } elseif ($pemeriksaan) {
                $pemeriksaan->update([
                    'id_wilayah_kerja' => $data['id_wilayah_kerja'],
                    'id_bidan' => $data['id_bidan'],
                    'catatan' => $data['catatan'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Hapus jadwal.
     */
    public function destroy($id)
    {
        $admin = $this->adminLogin();

        $jadwal = $this->jadwalMilikAdmin($id, $admin);

        $posyandu = JadwalPosyandu::where('id_jadwal', $jadwal->id_jadwal)->first();

        if ($posyandu) {
            $kegiatan = KegiatanPosyandu::find($posyandu->id_kegiatan);

            if ($kegiatan && DB::table('pemeriksaan_posyandu')
                ->where('kegiatan_posyandu_id', $kegiatan->id)
                ->exists()) {
                return redirect()
                    ->route('admin.jadwal.index')
                    ->with('error', 'Jadwal tidak dapat dihapus karena sudah memiliki data pemeriksaan Posyandu.');
            }
        }

        DB::transaction(function () use ($jadwal, $posyandu) {
            Notifikasi::where('id_jadwal', $jadwal->id_jadwal)->delete();

            if ($posyandu) {
                $kegiatan = KegiatanPosyandu::find($posyandu->id_kegiatan);

                $posyandu->delete();

                if ($kegiatan) {
                    $kegiatan->delete();
                }
            } else {
                JadwalPemeriksaan::where('id_jadwal', $jadwal->id_jadwal)->delete();
            }

            $jadwal->delete();
        });

        return redirect()
            ->route('admin.jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}
