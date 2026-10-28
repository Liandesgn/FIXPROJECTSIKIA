@php
    $tabs = [
        'wilayah'   => ['label' => 'Wilayah',   'route' => 'admin.wilayah-kerja.index'],
        'posyandu'  => ['label' => 'Posyandu',  'route' => 'admin.posyandu.index'],
        'penugasan' => ['label' => 'Penugasan', 'route' => 'admin.penugasan.index'],
    ];
@endphp

<div class="flex gap-2 border-b border-[#F1D5E0]">
    @foreach ($tabs as $key => $tab)
        @if (\Illuminate\Support\Facades\Route::has($tab['route']))
            <a href="{{ route($tab['route']) }}"
               class="rounded-t-lg px-4 py-2 text-sm font-semibold transition
                      {{ $active === $key ? 'bg-[#C9517E] text-white' : 'text-[#542D3D] hover:bg-[#FCE7F0]' }}">
                {{ $tab['label'] }}
            </a>
        @else
            <span class="cursor-not-allowed rounded-t-lg px-4 py-2 text-sm font-semibold text-slate-400" title="Segera hadir">
                {{ $tab['label'] }}
            </span>
        @endif
    @endforeach
</div>