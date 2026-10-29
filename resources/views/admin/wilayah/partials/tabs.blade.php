{{-- Navigasi tab Kelola Wilayah Kerja. Parameter: $active (wilayah|posyandu|penugasan) --}}
@php
    $active = $active ?? 'wilayah';
    $tabs = [
        'wilayah'   => ['label' => 'Wilayah',   'route' => 'admin.wilayah-kerja.index'],
        'posyandu'  => ['label' => 'Posyandu',  'route' => 'admin.posyandu.index'],
        'penugasan' => ['label' => 'Penugasan', 'route' => 'admin.penugasan.index'],
    ];
@endphp

<div class="flex gap-2 overflow-x-auto border-b-2 border-[#F1D5E0]" role="tablist">
    @foreach ($tabs as $key => $tab)
        @if (\Illuminate\Support\Facades\Route::has($tab['route']))
            <a href="{{ route($tab['route']) }}" role="tab" @if ($active === $key) aria-current="page" @endif
               class="-mb-0.5 whitespace-nowrap rounded-t-xl px-6 py-2.5 text-sm font-bold transition focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40
                      {{ $active === $key ? 'bg-[#C9517E] text-white shadow-sm' : 'text-[#542D3D] hover:bg-[#FCE7F0]' }}">
                {{ $tab['label'] }}
            </a>
        @else
            <span class="-mb-0.5 cursor-not-allowed whitespace-nowrap rounded-t-xl px-6 py-2.5 text-sm font-semibold text-slate-500" title="Segera hadir">
                {{ $tab['label'] }}
            </span>
        @endif
    @endforeach
</div>