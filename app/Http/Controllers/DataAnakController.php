<?php

namespace App\Http\Controllers;

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

    // Tampilan khusus Ibu: seluruh detail anak dalam satu halaman.
    if ($user->isOrangTua()) {
        $ibu = Ibu::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Ambil semua anak milik Ibu yang sedang login.
        $anaks = $ibu->anak()
            ->with(['ibu.user', 'ibu.wilayahKerja'])
            ->orderBy('tgl_lahir')
            ->get();

        // Ambil riwayat pemeriksaan seluruh anak sekaligus.
        $riwayatSemuaAnak = PemeriksaanPosyandu::query()
            ->with('kegiatan')
            ->whereIn('anak_id', $anaks->modelKeys())
            ->whereHas('kegiatan')
            ->get()
            ->groupBy('anak_id');

        // Siapkan detail dan riwayat masing-masing anak.
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

    // Tampilan Admin, Bidan, dan Kader tetap seperti sebelumnya.
    $anaks = Anak::query()
        ->visibleTo($user)
        ->with('ibu.user')
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = '%'.$request->string('search').'%';

            $query->where(fn ($q) => $q
                ->where('nama_anak', 'like', $search)
                ->orWhere('nik_anak', 'like', $search));
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('data-anak.index', [
        'anaks' => $anaks,
        'base' => $this->base($request),
    ]);
}

    public function create(Request $request): View
    {
        return view('data-anak.create', [
            'ibus' => $this->ibus($request->user()),
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

    private function base(Request $request): string
    {
        return Str::before($request->route()->getName(), '.');
    }

    private function ibus($user)
    {
        return Ibu::query()
            ->visibleTo($user)
            ->with('user')
            ->get()
            ->sortBy(fn ($i) => $i->user?->name)
            ->values();
    }
}