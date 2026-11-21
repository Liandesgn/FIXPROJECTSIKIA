@php
    $hamil = $status === \App\Enums\StatusKehamilanEnum::Hamil;
    $ada   = $status !== null;

    $warna = $hamil ? 'bg-emerald-100 text-emerald-800' : ($ada ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-600');
    $titik = $hamil ? 'bg-emerald-500' : ($ada ? 'bg-sky-500' : 'bg-slate-400');
@endphp
<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $warna }}">
    <span class="h-1.5 w-1.5 rounded-full {{ $titik }}"></span>
    {{ $status?->label() ?? '-' }}
</span>