<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PenugasanRequest;
use App\Models\Bidan;
use App\Models\Kader;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PenugasanController extends Controller
{
    public function index(Request $request): View
    {
        $wilayahs = WilayahKerja::query()
            ->when($request->filled('search'), fn ($q) => $q
                ->where('nama_wilayah', 'like', '%'.$request->string('search').'%'))
            ->orderBy('kecamatan')
            ->orderBy('desa')
            ->get();

        return view('admin.penugasan.index', [
            'wilayahs' => $wilayahs,
            'bidanMap' => Bidan::with('user')->get()->keyBy('wilayah_kerja_id'),
            'kaderMap' => Kader::with('user')->get()->groupBy('wilayah_kerja_id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.penugasan.create', $this->formData());
    }

    public function store(PenugasanRequest $request): RedirectResponse
    {
        $wilayah = WilayahKerja::findOrFail($request->integer('wilayah_kerja_id'));
        $this->simpan($wilayah, $request->validated());

        return redirect()
            ->route('admin.penugasan.index')
            ->with('success', 'Penugasan bidan penanggung jawab berhasil disimpan. Penugasan kader wilayah berhasil disimpan.');
    }

    /**
     * Penugasan diperbarui sesuai pilihan terbaru:
     * - Bidan: bidan terpilih ditugaskan ke wilayah ini, bidan lama di wilayah ini dilepas.
     * - Kader: yang dicentang ditugaskan, yang tidak dicentang dilepas.
     */
    private function simpan(WilayahKerja $wilayah, array $data): void
    {
        DB::transaction(function () use ($wilayah, $data) {
            // Bidan
            $bidanBaru = User::findOrFail($data['bidan_id']);
            $bidanLama = Bidan::with('user')->where('wilayah_kerja_id', $wilayah->id)->first();

            if ($bidanLama && (int) $bidanLama->user_id !== (int) $bidanBaru->id) {
                $bidanLama->user->lepasWilayah();
            }

            $bidanBaru->assignWilayah($wilayah->id);

            // Kader
            $kaderBaru = collect($data['kader_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
            $kaderLama = Kader::where('wilayah_kerja_id', $wilayah->id)
                ->pluck('user_id')->map(fn ($id) => (int) $id);

            User::whereIn('id', $kaderLama->diff($kaderBaru)->values())->get()
                ->each->lepasWilayah();

            User::whereIn('id', $kaderBaru->diff($kaderLama)->values())->get()
                ->each->assignWilayah($wilayah->id);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $wilayahs = WilayahKerja::query()->orderBy('kecamatan')->orderBy('desa')->get();

        // Penugasan tiap wilayah, agar bidan & kader lama langsung terpilih di form
        $bidanPer = Bidan::whereNotNull('wilayah_kerja_id')->pluck('user_id', 'wilayah_kerja_id');
        $kaderPer = Kader::whereNotNull('wilayah_kerja_id')->get()->groupBy('wilayah_kerja_id');

        $peta = [];
        foreach ($wilayahs as $w) {
            $peta[$w->id] = [
                'bidan' => isset($bidanPer[$w->id]) ? (int) $bidanPer[$w->id] : null,
                'kader' => ($kaderPer[$w->id] ?? collect())->pluck('user_id')->map(fn ($v) => (int) $v)->values()->all(),
            ];
        }

        return [
            'wilayahs' => $wilayahs,
            'bidans' => User::role(RoleEnum::Bidan->value)->with('bidan.wilayahKerja')->orderBy('name')->get(),
            'kaders' => User::role(RoleEnum::Kader->value)->with('kader.wilayahKerja')->orderBy('name')->get(),
            'penugasanMap' => $peta,
        ];
    }
}