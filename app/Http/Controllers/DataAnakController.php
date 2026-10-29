<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\AnakRequest;
use App\Models\Anak;
use App\Models\Ibu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DataAnakController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Role Ibu: tampilan kartu sesuai wireframe 5.14
        if ($user->isOrangTua()) {
            $ibu = Ibu::with(['user', 'wilayahKerja', 'anak'])
                ->where('user_id', $user->id)
                ->first();

            return view('data-anak.ibu-sendiri', ['ibu' => $ibu]);
        }

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

    public function create(): View
    {
        return view('data-anak.create', ['ibus' => $this->ibus()]);
    }

    public function store(AnakRequest $request): RedirectResponse
    {
        Anak::create($request->validated());

        return redirect()
            ->route('admin.data-anak.index')
            ->with('success', 'Data anak berhasil ditambahkan.');
    }

    public function show(Request $request, Anak $anak): View|RedirectResponse
    {
        $user = $request->user();

        // Kader hanya boleh mencari, tidak boleh membuka detail
        abort_if($user->hasRole(RoleEnum::Kader->value), 403, 'Akses ditolak.');

        abort_unless(
            Anak::visibleTo($user)->whereKey($anak->id)->exists(),
            403,
            'Akses ditolak.'
        );

        // Ibu: semua data anaknya sudah tampil di halaman Data Anak
        if ($user->isOrangTua()) {
            return redirect()->route('ibu.data-anak.index');
        }

        return view('data-anak.show', [
            'anak' => $anak->load('ibu.user', 'ibu.wilayahKerja'),
            'base' => $this->base($request),
        ]);
    }

    public function edit(Anak $anak): View
    {
        return view('data-anak.edit', [
            'anak' => $anak,
            'ibus' => $this->ibus(),
        ]);
    }

    public function update(AnakRequest $request, Anak $anak): RedirectResponse
    {
        $anak->update($request->validated());

        return redirect()
            ->route('admin.data-anak.index')
            ->with('success', 'Data anak berhasil diperbarui.');
    }

    private function base(Request $request): string
    {
        return Str::before($request->route()->getName(), '.');
    }

    private function ibus()
    {
        return Ibu::with('user')->get()->sortBy(fn ($i) => $i->user?->name)->values();
    }
}