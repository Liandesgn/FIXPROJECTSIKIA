<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Rw;
use App\Enums\StatusKehamilanEnum;
use App\Enums\RoleEnum;
use App\Http\Requests\AnakRequest;
use App\Models\Anak;
use App\Models\Ibu;
use App\Models\PemeriksaanPosyandu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DataAnakController extends Controller
{
   

public function index(Request $request): View
{
    $user = $request->user();

    // Ibu hanya melihat data anak miliknya sendiri.
    if ($user->isOrangTua()) {
        $ibu = Ibu::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $anaks = $ibu->anak()
            ->with(['ibu.user', 'ibu.wilayahKerja'])
            ->orderBy('tgl_lahir')
            ->get();

        $riwayatSemuaAnak = PemeriksaanPosyandu::query()
            ->with('kegiatan')
            ->whereIn('anak_id', $anaks->modelKeys())
            ->whereHas('kegiatan')
            ->get()
            ->groupBy('anak_id');

        $detailAnak = $anaks->map(function ($anak) use ($riwayatSemuaAnak) {
            $riwayatPemeriksaan = ($riwayatSemuaAnak->get($anak->id) ?? collect())
                ->sortBy(
                    fn ($item) => $item->kegiatan?->tanggal?->format('Y-m-d') ?? ''
                )
                ->values();

            return [
                'anak' => $anak,
                'riwayatPemeriksaan' => $riwayatPemeriksaan,
                'pemeriksaanTerakhir' => $riwayatPemeriksaan->last(),
            ];
        });

        return view('data-anak.ibu-semua', [
            'detailAnak' => $detailAnak,
            'base' => 'ibu',
        ]);
    }

    // Data Anak untuk Admin, Bidan, dan Kader.
    $anaks = Anak::query()
        ->visibleTo($user)
        ->with(['ibu.user', 'ibu.wilayahKerja'])
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = '%' . $request->input('search') . '%';

            $query->where(function ($q) use ($search) {
                $q->where('nama_anak', 'like', $search)
                    ->orWhere('nik_anak', 'like', $search);
            });
        })
        ->when($request->filled('wilayah_kerja_id'), function ($query) use ($request, $user) {
            // Pastikan wilayah yang dipilih termasuk cakupan pengguna.
            $wilayahId = $this->wilayahQuery($user)
                ->whereKey($request->integer('wilayah_kerja_id'))
                ->value('id');

            $query->whereHas('ibu', function ($ibuQuery) use ($wilayahId) {
                if ($wilayahId) {
                    $ibuQuery->where('wilayah_kerja_id', $wilayahId);
                } else {
                    $ibuQuery->whereRaw('1 = 0');
                }
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('data-anak.index', [
        'anaks' => $anaks,
        'wilayahs' => $this->wilayahs($user),
        'base' => $this->base($request),
    ]);
}



public function create(Request $request): View
{
    $ibus = $this->ibus($request->user());

    $selectedIbuId = $request->query('ibu_id');

    if ($selectedIbuId) {
        $ibuTerpilih = $ibus->firstWhere('id', (int) $selectedIbuId);

        abort_unless($ibuTerpilih, 403, 'Ibu tidak ditemukan atau akses ditolak.');
    }

    return view('data-anak.create', [
        'ibus' => $ibus,
        'selectedIbuId' => $selectedIbuId,
    ]);
}

public function store(AnakRequest $request): RedirectResponse
{
    $data = $request->validated();

    abort_unless(
        Ibu::visibleTo($request->user())
            ->whereKey($data['ibu_id'])
            ->exists(),
        403,
        'Akses ditolak.'
    );

    Anak::create($data);

    $kembaliKeIbuId = $request->input('kembali_ke_ibu_id');

    if ($kembaliKeIbuId) {
        $ibu = Ibu::visibleTo($request->user())
            ->whereKey($kembaliKeIbuId)
            ->first();

        if ($ibu && (int) $ibu->id === (int) $data['ibu_id']) {
            return redirect()
                ->route('admin.data-ibu.show', $ibu)
                ->with('success', 'Data anak berhasil ditambahkan.');
        }
    }

    return redirect()
        ->route($this->base($request).'.data-anak.index')
        ->with('success', 'Data anak berhasil ditambahkan.');
}

   
public function show(Request $request, Anak $anak): View
{
    $user = $request->user();

    // Pastikan anak termasuk dalam cakupan akses pengguna.
    // Admin, Bidan, dan Kader dibatasi berdasarkan wilayah.
    // Ibu hanya dapat melihat anak miliknya sendiri.
    abort_unless(
        Anak::visibleTo($user)
            ->whereKey($anak->id)
            ->exists(),
        403,
        'Akses ditolak.'
    );

    // Ambil data anak dan ibu yang terhubung.
    $anak->load([
        'ibu.user',
        'ibu.wilayahKerja',
    ]);

    // Ambil seluruh riwayat pemeriksaan anak.
    $riwayatPemeriksaan = PemeriksaanPosyandu::query()
        ->with('kegiatan')
        ->where('anak_id', $anak->id)
        ->whereHas('kegiatan')
        ->get()
        ->sortBy(function ($pemeriksaan) {
            return $pemeriksaan->kegiatan?->tanggal?->format('Y-m-d') ?? '';
        })
        ->values();

    // Pemeriksaan terakhir.
    $pemeriksaanTerakhir = $riwayatPemeriksaan->last();

    return view('data-anak.show', [
        'anak' => $anak,
        'base' => $this->base($request),
        'riwayatPemeriksaan' => $riwayatPemeriksaan,
        'pemeriksaanTerakhir' => $pemeriksaanTerakhir,
    ]);
}


public function detailStatus(Request $request, Anak $anak): View
{
    $user = $request->user();

    // Pastikan anak termasuk dalam cakupan akses pengguna.
    abort_unless(
        Anak::visibleTo($user)
            ->whereKey($anak->id)
            ->exists(),
        403,
        'Akses ditolak.'
    );

    // Ambil data anak.
    $anak->load('ibu.user');

    // Ambil pemeriksaan terakhir berdasarkan tanggal kegiatan Posyandu.
    $pemeriksaanTerakhir = PemeriksaanPosyandu::query()
        ->with('kegiatan')
        ->where('anak_id', $anak->id)
        ->whereHas('kegiatan')
        ->get()
        ->sortBy(
            fn ($item) => $item->kegiatan?->tanggal?->format('Y-m-d') ?? ''
        )
        ->last();

    return view('data-anak.detail-status', [
        'anak' => $anak,
        'base' => $this->base($request),
        'pemeriksaan' => $pemeriksaanTerakhir,
    ]);
}



    public function edit(Request $request, Anak $anak): View
    {
        abort_unless(
            Anak::visibleTo($request->user())->whereKey($anak->id)->exists(),
            403,
            'Akses ditolak.'
        );

        return view('data-anak.edit', [
            'anak' => $anak,
            'ibus' => $this->ibus($request->user()),
        ]);
    }

    public function update(AnakRequest $request, Anak $anak): RedirectResponse
    {
        $data = $request->validated();

        abort_unless(
            Anak::visibleTo($request->user())->whereKey($anak->id)->exists(),
            403,
            'Akses ditolak.'
        );

        abort_unless(
            Ibu::visibleTo($request->user())
                ->whereKey($data['ibu_id'])
                ->exists(),
            403,
            'Akses ditolak.'
        );

        $anak->update($data);

        return redirect()
            ->route($this->base($request).'.data-anak.index')
            ->with('success', 'Data anak berhasil diperbarui.');
    }

/**
 * Mengambil wilayah yang boleh diakses pengguna.
 *
 * Admin      : wilayah dalam Puskesmas miliknya.
 * SuperAdmin : seluruh wilayah.
 * Bidan      : wilayah penugasannya.
 * Kader      : wilayah penugasannya.
 */
private function wilayahQuery(User $user): Builder
{
    $query = WilayahKerja::query();

    if ($user->isSuperAdmin()) {
        return $query;
    }

    if ($user->isAdmin()) {
        $user->loadMissing('admin');

        $puskesmasId = $user->admin?->puskesmas_id;

        return $puskesmasId
            ? $query->where('puskesmas_id', $puskesmasId)
            : $query->whereRaw('1 = 0');
    }

    $user->loadMissing(['bidan', 'kader']);

    $wilayahId = $user->bidan?->wilayah_kerja_id
        ?? $user->kader?->wilayah_kerja_id
        ?? $user->wilayah_kerja_id;

    return $wilayahId
        ? $query->where('id', $wilayahId)
        : $query->whereRaw('1 = 0');
}

/**
 * Mengambil daftar wilayah sesuai hak akses pengguna.
 */
private function wilayahs(User $user)
{
    return $this->wilayahQuery($user)
        ->orderBy('nama_wilayah')
        ->get();
}


    private function base(Request $request): string
    {
        return Str::before($request->route()->getName(), '.');
    }

    private function ibus($user)
    {
        return Ibu::query()
            ->visibleTo($user)
            ->where(
                'status_kehamilan',
                StatusKehamilanEnum::Melahirkan->value
            )
            ->with('user')
            ->get()
            ->sortBy(fn ($i) => $i->user?->name)
            ->values();
    }
}