@php $hamil = $status === \App\Enums\StatusKehamilanEnum::Hamil; @endphp
<span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $hamil ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
    {{ $status?->label() ?? '-' }}
</span>