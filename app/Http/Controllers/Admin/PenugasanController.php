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
            ->with('success', 'Penugasan bidan dan kader berhasil disimpan.');
    }

    public function edit(WilayahKerja $wilayah): View
    {
        return view('admin.penugasan.edit', $this->formData() + [
            'wilayah' => $wilayah,
            'bidanSekarang' => Bidan::where('wilayah_kerja_id', $wilayah->id)->value('user_id'),
            'kaderSekarang' => Kader::where('wilayah_kerja_id', $wilayah->id)->pluck('user_id')->all(),
        ]);
    }

    public function update(PenugasanRequest $request, WilayahKerja $wilayah): RedirectResponse
    {
        $this->simpan($wilayah, $request->validated());

        return redirect()
            ->route('admin.penugasan.index')
            ->with('success', 'Penugasan bidan dan kader berhasil diperbarui.');
    }

        public function destroy(WilayahKerja $wilayah): RedirectResponse
    {
        $userIds = Bidan::where('wilayah_kerja_id', $wilayah->id)->pluck('user_id')
            ->merge(Kader::where('wilayah_kerja_id', $wilayah->id)->pluck('user_id'));

        if ($userIds->isEmpty()) {
            return back()->with('error', 'Wilayah ini belum memiliki penugasan.');
        }

        DB::transaction(function () use ($userIds) {
            User::whereIn('id', $userIds)->get()->each->lepasWilayah();
        });

        return redirect()
            ->route('admin.penugasan.index')
            ->with('success', 'Penugasan wilayah berhasil dihapus.');
    }   


    /**
     * Satu bidan per wilayah, banyak kader per wilayah.
     * Bidan baru yang dipilih menukar posisi dengan bidan lama (kalau ada),
     * supaya tidak ada wilayah dengan dua bidan dan tidak ada bidan tanpa wilayah.
     */
    private function simpan(WilayahKerja $wilayah, array $data): void
    {
        DB::transaction(function () use ($wilayah, $data) {
            $bidanBaru = User::findOrFail($data['bidan_id']);
            $bidanLama = Bidan::with('user')->where('wilayah_kerja_id', $wilayah->id)->first();

            if ($bidanLama && $bidanLama->user_id !== $bidanBaru->id) {
                $wilayahAsalBaru = $bidanBaru->bidan?->wilayah_kerja_id;

                if ($wilayahAsalBaru) {
                    $bidanLama->user->assignWilayah($wilayahAsalBaru);
                }
            }

            $bidanBaru->assignWilayah($wilayah->id);

            foreach ($data['kader_ids'] ?? [] as $kaderId) {
                User::findOrFail($kaderId)->assignWilayah($wilayah->id);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'wilayahs' => WilayahKerja::query()->orderBy('kecamatan')->orderBy('desa')->get(),
            'bidans' => User::role(RoleEnum::Bidan->value)->with('bidan.wilayahKerja')->orderBy('name')->get(),
            'kaders' => User::role(RoleEnum::Kader->value)->with('kader.wilayahKerja')->orderBy('name')->get(),
        ];
    }
}