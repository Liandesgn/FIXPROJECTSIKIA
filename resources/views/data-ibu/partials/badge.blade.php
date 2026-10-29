@php $hamil = $status === \App\Enums\StatusKehamilanEnum::Hamil; @endphp
<span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $hamil ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-800' }}">
    {{ $status?->label() ?? '-' }}
</span>