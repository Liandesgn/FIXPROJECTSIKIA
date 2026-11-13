@php
    use App\Enums\UserStatusEnum;
@endphp

@if ($status === UserStatusEnum::Active)
    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
        Aktif
    </span>
@else
    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-800">
        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
        Nonaktif
    </span>
@endif