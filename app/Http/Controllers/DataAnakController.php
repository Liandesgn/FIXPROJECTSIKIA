<?php

namespace App\Http\Controllers;

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
        $anaks = Anak::query()
            ->visibleTo($request->user())
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

    public function show(Request $request, Anak $anak): View
    {
        abort_unless(
            Anak::visibleTo($request->user())->whereKey($anak->id)->exists(),
            403,
            'Akses ditolak.'
        );

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