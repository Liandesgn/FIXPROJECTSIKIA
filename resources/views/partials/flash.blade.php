@if (session('success'))
    <div class="mb-4 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <span class="font-semibold">Berhasil:</span> <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <span class="font-semibold">Gagal:</span> <span>{{ session('error') }}</span>
    </div>
@endif
