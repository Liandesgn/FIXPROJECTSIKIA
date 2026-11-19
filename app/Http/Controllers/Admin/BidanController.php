<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePetugasRequest;
use App\Http\Requests\Admin\UpdatePetugasRequest;
use App\Models\Bidan;
use App\Models\User;
use App\Models\WilayahKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BidanController extends Controller
{
    public function index(Request $request): View
    {
        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if($puskesmasId === null, 403, 'Admin belum memiliki Puskesmas.');

        $search = trim((string) $request->query('search', ''));

        $bidans = User::query()
            ->role(RoleEnum::Bidan->value)
            ->with([
                'bidan.wilayahKerja.kelurahanDesa',
                'bidan.wilayahKerja.puskesmas',
            ])
            ->whereHas('bidan.wilayahKerja', function ($query) use ($puskesmasId) {
                $query->where('puskesmas_id', $puskesmasId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('wilayah_kerja_id'), function ($query) use ($request, $puskesmasId) {
                $query->whereHas('bidan.wilayahKerja', function ($wilayahQuery) use ($request, $puskesmasId) {
                    $wilayahQuery
                        ->where('puskesmas_id', $puskesmasId)
                        ->where('id', $request->integer('wilayah_kerja_id'));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bidan.index', [
            'bidans' => $bidans,
            'wilayahs' => $this->wilayahs($puskesmasId),
        ]);
    }

    public function create(Request $request): View
    {
        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if($puskesmasId === null, 403, 'Admin belum memiliki Puskesmas.');

        return view('admin.bidan.create', [
            'bidan' => new User(),
            'wilayahs' => $this->wilayahs($puskesmasId),
        ]);
    }

    public function store(StorePetugasRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if($puskesmasId === null, 403, 'Admin belum memiliki Puskesmas.');

        $this->pastikanWilayahMilikPuskesmas(
            (int) $data['wilayah_kerja_id'],
            $puskesmasId
        );

        DB::transaction(function () use ($data) {
            $bidan = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'status' => UserStatusEnum::Active->value,
            ]);

            $bidan->assignRole(RoleEnum::Bidan->value);
            $bidan->assignWilayah((int) $data['wilayah_kerja_id']);
        });

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil ditambahkan.');
    }

    public function edit(Request $request, User $bidan): View
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if($puskesmasId === null, 403, 'Admin belum memiliki Puskesmas.');

        $this->pastikanBidanMilikPuskesmas($bidan, $puskesmasId);

        return view('admin.bidan.edit', [
            'bidan' => $bidan->load([
                'bidan.wilayahKerja.kelurahanDesa',
                'bidan.wilayahKerja.puskesmas',
            ]),
            'bidans' => $bidan,
            'wilayahs' => $this->wilayahs($puskesmasId),
        ]);
    }

    public function update(
        UpdatePetugasRequest $request,
        User $bidan
    ): RedirectResponse {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        $puskesmasId = $request->user()->managedPuskesmasId();

        abort_if($puskesmasId === null, 403, 'Admin belum memiliki Puskesmas.');

        $this->pastikanBidanMilikPuskesmas($bidan, $puskesmasId);

        $data = $request->validated();

        $this->pastikanWilayahMilikPuskesmas(
            (int) $data['wilayah_kerja_id'],
            $puskesmasId
        );

        DB::transaction(function () use ($data, $bidan) {
            $bidan->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $bidan->password = $data['password'];
            }

            $bidan->save();

            $bidan->assignWilayah((int) $data['wilayah_kerja_id']);
        });

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil diperbarui.');
    }

    public function destroy(User $bidan): RedirectResponse
    {
        abort_unless($bidan->hasRole(RoleEnum::Bidan->value), 404);

        $puskesmasId = auth()->user()->managedPuskesmasId();

        abort_if($puskesmasId === null, 403, 'Admin belum memiliki Puskesmas.');

        $this->pastikanBidanMilikPuskesmas($bidan, $puskesmasId);

        DB::transaction(function () use ($bidan) {
            $bidan->tokens()->delete();
            $bidan->bidan()->delete();
            $bidan->syncRoles([]);
            $bidan->delete();
        });

        return redirect()
            ->route('admin.bidan.index')
            ->with('success', 'Data bidan berhasil dihapus.');
    }

    /**
     * Mengambil seluruh wilayah kerja dalam Puskesmas Admin.
     */
    private function wilayahs(int $puskesmasId)
    {
        return WilayahKerja::query()
            ->with(['kecamatan', 'puskesmas', 'kelurahanDesa'])
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
                'wilayah_kerja_id' => 'Wilayah yang dipilih tidak termasuk Puskesmas Anda.',
            ]);
        }
    }

    /**
     * Memastikan Bidan yang dikelola berada di Puskesmas Admin.
     */
    private function pastikanBidanMilikPuskesmas(
        User $bidan,
        int $puskesmasId
    ): void {
        $sesuai = Bidan::query()
            ->where('user_id', $bidan->id)
            ->whereHas('wilayahKerja', function ($query) use ($puskesmasId) {
                $query->where('puskesmas_id', $puskesmasId);
            })
            ->exists();

        abort_unless($sesuai, 403);
    }
}
