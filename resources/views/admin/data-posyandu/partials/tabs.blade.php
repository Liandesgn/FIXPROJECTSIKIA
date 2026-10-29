@php
    $tabs = [
        'kegiatan' => ['label' => 'Kegiatan', 'route' => 'admin.data-posyandu.index'],
        'rekap'    => ['label' => 'Rekap',    'route' => 'admin.data-posyandu.rekap'],
    ];
@endphp

<div class="flex gap-2 border-b-2 border-[#F1D5E0]" role="tablist">
    @foreach ($tabs as $key => $tab)
        <a href="{{ route($tab['route']) }}" role="tab" @if ($active === $key) aria-current="page" @endif
           class="-mb-0.5 rounded-t-xl px-6 py-2.5 text-sm font-bold transition focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40
                  {{ $active === $key ? 'bg-[#C9517E] text-white shadow-sm' : 'text-[#542D3D] hover:bg-[#FCE7F0]' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>