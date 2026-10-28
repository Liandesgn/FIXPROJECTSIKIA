@php
    $tabs = [
        'kegiatan' => ['label' => 'Kegiatan', 'route' => 'admin.data-posyandu.index'],
        'rekap'    => ['label' => 'Rekap',    'route' => 'admin.data-posyandu.rekap'],
    ];
@endphp

<div class="flex gap-2 border-b border-[#F1D5E0]">
    @foreach ($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}"
           class="rounded-t-lg px-4 py-2 text-sm font-semibold transition
                  {{ $active === $key ? 'bg-[#C9517E] text-white' : 'text-[#542D3D] hover:bg-[#FCE7F0]' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>