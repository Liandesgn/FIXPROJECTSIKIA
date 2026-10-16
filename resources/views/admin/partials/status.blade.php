@php
    use App\Enums\UserStatusEnum;
@endphp

@if ($status === UserStatusEnum::Active)
    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
        Aktif
    </span>
@else
    <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-medium text-slate-600">
        Nonaktif
    </span>
@endif
