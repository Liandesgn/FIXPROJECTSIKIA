<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePetugasRequest;
use App\Http\Requests\Admin\UpdatePetugasRequest;
use App\Models\Kader;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KaderController extends Controller
{
    /**
     * Menampilkan daftar Kader dalam Puskesmas Admin.
     */
    public function index(Request $request): View
    {
        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if(
            $puskesmasId === null,
            403,
            'Admin belum memiliki Puskesmas.'
        );

        $search = trim((string) $request->query('search', ''));

        $kaders = User::query()
            ->role(RoleEnum::Kader->value)
            ->with([
                'kader.wilayahKerja.kelurahanDesa',
                'kader.wilayahKerja.puskesmas',
            ])
            ->whereHas('kader.wilayahKerja', function ($query) use ($puskesmasId) {
                $query->where('puskesmas_id', $puskesmasId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('wilayah_kerja_id'), function ($query) use ($request, $puskesmasId) {
                $query->whereHas('kader.wilayahKerja', function ($wilayahQuery) use ($request, $puskesmasId) {
                    $wilayahQuery
                        ->where('puskesmas_id', $puskesmasId)
                        ->where('id', $request->integer('wilayah_kerja_id'));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kader.index', [
            'kaders' => $kaders,
            'wilayahs' => $this->wilayahs($puskesmasId),
        ]);
    }

    /**
     * Menampilkan form tambah Kader.
     */
    public function create(Request $request): View
    {
        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if(
            $puskesmasId === null,
            403,
            'Admin belum memiliki Puskesmas.'
        );

        return view('admin.kader.create', [
            'kader' => new User(),
            'wilayahs' => $this->wilayahs($puskesmasId),
        ]);
    }

    /**
     * Menyimpan Kader baru.
     */
    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if(
            $puskesmasId === null,
            403,
            'Admin belum memiliki Puskesmas.'
        );

        $wilayahId = (int) $data['wilayah_kerja_id'];

        $this->pastikanWilayahMilikPuskesmas(
            $wilayahId,
            $puskesmasId
        );

        DB::transaction(function () use ($data, $wilayahId) {
            $this->pastikanKapasitasKader($wilayahId);

            $kader = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $kader->assignRole(RoleEnum::Kader->value);
            $kader->assignWilayah($wilayahId);
        });

        return redirect()
            ->route('admin.kader.index')
            ->with('success', 'Data kader berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit Kader.
     */
    public function edit(Request $request, User $kader): View
    {
        abort_unless(
            $kader->hasRole(RoleEnum::Kader->value),
            404
        );

        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if(
            $puskesmasId === null,
            403,
            'Admin belum memiliki Puskesmas.'
        );

        $this->pastikanKaderMilikPuskesmas(
            $kader,
            $puskesmasId
        );

        return view('admin.kader.edit', [
            'kader' => $kader->load([
                'kader.wilayahKerja.kelurahanDesa',
                'kader.wilayahKerja.puskesmas',
            ]),
            'wilayahs' => $this->wilayahs($puskesmasId),
        ]);
    }

    /**
     * Memperbarui data Kader.
     */
    public function update(
        UpdatePetugasRequest $request,
        User $kader
    ): RedirectResponse {
        abort_unless(
            $kader->hasRole(RoleEnum::Kader->value),
            404
        );

        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if(
            $puskesmasId === null,
            403,
            'Admin belum memiliki Puskesmas.'
        );

        $this->pastikanKaderMilikPuskesmas(
            $kader,
            $puskesmasId
        );

        $data = $request->validated();

        $wilayahId = (int) $data['wilayah_kerja_id'];

        $this->pastikanWilayahMilikPuskesmas(
            $wilayahId,
            $puskesmasId
        );

        DB::transaction(function () use ($data, $kader, $wilayahId) {
            $currentKaderId = $kader->kader?->id;

            $this->pastikanKapasitasKader(
                $wilayahId,
                $currentKaderId
            );

            $kader->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $kader->password = $data['password'];
            }

            $kader->save();

            $kader->assignWilayah($wilayahId);
        });

        return redirect()
            ->route('admin.kader.index')
            ->with('success', 'Data kader berhasil diperbarui.');
    }

    /**
     * Menghapus akun Kader.
     */
    public function destroy(User $kader): RedirectResponse
    {
        abort_unless(
            $kader->hasRole(RoleEnum::Kader->value),
            404
        );

        $puskesmasId = auth()->user()->managedPuskesmasId();

        abort_if(
            $puskesmasId === null,
            403,
            'Admin belum memiliki Puskesmas.'
        );

        $this->pastikanKaderMilikPuskesmas(
            $kader,
            $puskesmasId
        );

        if ($kader->id === auth()->id()) {
            return back()->with(
                'error',
                'Anda tidak dapat menghapus akun sendiri.'
            );
        }

        DB::transaction(function () use ($kader) {
            $kader->tokens()->delete();
            $kader->kader()->delete();
            $kader->syncRoles([]);
            $kader->delete();
        });

        return redirect()
            ->route('admin.kader.index')
            ->with('success', 'Data kader berhasil dihapus.');
    }

    /**
     * Mengambil seluruh wilayah kerja dalam Puskesmas Admin.
     */
    private function wilayahs(int $puskesmasId)
    {
        return WilayahKerja::query()
            ->with([
                'kecamatan',
                'puskesmas',
                'kelurahanDesa',
            ])
            ->where('puskesmas_id', $puskesmasId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Memastikan wilayah yang dipilih berada di Puskesmas Admin.
     */
    private function pastikanWilayahMilikPuskesmas(
        int $wilayahId,
        int $puskesmasId
    ): void {
        $sesuai = WilayahKerja::query()
            ->whereKey($wilayahId)
            ->where('puskesmas_id', $puskesmasId)
            ->exists();

        if (! $sesuai) {
            throw ValidationException::withMessages([
                'wilayah_kerja_id' =>
                    'Wilayah yang dipilih tidak termasuk Puskesmas Anda.',
            ]);
        }
    }

    /**
     * Memastikan Kader yang dikelola berada di Puskesmas Admin.
     */
    private function pastikanKaderMilikPuskesmas(
        User $kader,
        int $puskesmasId
    ): void {
        $sesuai = Kader::query()
            ->where('user_id', $kader->id)
            ->whereHas('wilayahKerja', function ($query) use ($puskesmasId) {
                $query->where('puskesmas_id', $puskesmasId);
            })
            ->exists();

        abort_unless($sesuai, 403);
    }

    /**
     * Memastikan satu wilayah tidak memiliki lebih dari 5 Kader.
     *
     * Harus dipanggil di dalam DB::transaction().
     */
    private function pastikanKapasitasKader(
        int $wilayahId,
        ?int $excludeKaderId = null
    ): void {
        WilayahKerja::query()
            ->whereKey($wilayahId)
            ->lockForUpdate()
            ->firstOrFail();

        $jumlahKader = Kader::query()
            ->where('wilayah_kerja_id', $wilayahId)
            ->when(
                $excludeKaderId !== null,
                fn ($query) => $query->where('id', '!=', $excludeKaderId)
            )
            ->count();

        if ($jumlahKader >= 5) {
            throw ValidationException::withMessages([
                'wilayah_kerja_id' =>
                    'Wilayah ini sudah mencapai batas maksimal 5 kader.',
            ]);
        }
    }
}
