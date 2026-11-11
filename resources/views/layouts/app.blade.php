<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FCE7F0">
    <title>@yield('title', 'Dashboard') — SI-KIA</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Tema SweetAlert2 SI-KIA (dipakai semua popup di aplikasi)
        window.SikiaSwal = Swal.mixin({
            buttonsStyling: false,
            reverseButtons: true,
            backdrop: 'rgba(84,45,61,0.5)',
            confirmButtonText: 'Kembali',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-3xl p-6 sm:p-8 shadow-2xl',
                title: 'text-lg font-bold leading-snug text-[#542D3D]',
                htmlContainer: 'text-sm leading-6 text-gray-500',
                actions: 'mt-5 gap-3',
                confirmButton: 'rounded-xl bg-[#C9517E] px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#C9517E]/25 transition hover:bg-[#B7436D] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/40',
                cancelButton: 'rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30'
            }
        });

        window.SikiaConfirm = function (opts, onConfirm) {
            const danger = !!opts.danger;

            // Jangan pernah mengirim customClass: undefined (akan menimpa tema di atas)
            const params = {
                icon: danger ? 'warning' : 'question',
                iconColor: '#C9517E',
                title: opts.title,
                text: opts.text,
                showCancelButton: true,
                confirmButtonText: opts.confirmText
            };

            // Hanya popup berbahaya (hapus) yang memakai tombol konfirmasi merah
            if (danger) {
                params.customClass = {
                    popup: 'rounded-3xl p-6 sm:p-8 shadow-2xl',
                    title: 'text-lg font-bold leading-snug text-[#542D3D]',
                    htmlContainer: 'text-sm leading-6 text-gray-500',
                    actions: 'mt-5 gap-3',
                    confirmButton: 'rounded-xl bg-red-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200',
                    cancelButton: 'rounded-xl border-2 border-[#E99AB7] bg-white px-6 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus:ring-4 focus:ring-[#E99AB7]/30'
                };
            }

            SikiaSwal.fire(params).then(function (r) { if (r.isConfirmed) onConfirm(); });
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $user      = auth()->user();
    $role      = $user->primaryRole();
    $roleKey   = $role ? Str::before($role->dashboard(), '.') : null;   // superadmin | admin | bidan | kader | orang-tua
    $roleLabel = $roleKey === 'orang-tua' ? 'Ibu' : $role?->label();    // tampilan: "Orang Tua" diganti "Ibu"
    $initial   = Str::upper(Str::substr($user->name, 0, 1));

    $dashboardUrl = $role ? route($role->dashboard()) : url('/');

    // Ikon (path SVG 24x24, stroke)
    $icons = [
        'home'     => 'M3 11.5 12 4l9 7.5M5 10v10h5v-6h4v6h5V10',
        'users'    => 'M16 11a3 3 0 1 0 0-6m-8 6a3 3 0 1 0 0-6m-6 13a6 6 0 0 1 12 0m2-5a6 6 0 0 1 6 5',
        'user'     => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0',
        'child'    => 'M12 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-4 14v-6l-2-4h12l-2 4v6',
        'building' => 'M3 21h18M5 21V8l7-5 7 5v13M10 21v-6h4v6h',
        'pin'      => 'M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Zm0-8.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
        'calendar' => 'M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z',
        'report'   => 'M8 3h8l4 4v14H4V3h4Zm0 9h8m-8 4h8',
        'chat'     => 'M21 11.5a8.5 8.5 0 0 1-12.8 7.3L3 20l1.2-4.2A8.5 8.5 0 1 1 21 11.5Z',
        'book'     => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z',
        'bell'     => 'M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9Zm4 9.5a2 2 0 0 0 4 0',
        'shield'   => 'M12 3l7 3v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V6l7-3Z',
        'key'      => 'M21 2l-2 2m-7.6 7.6a5.5 5.5 0 1 1-7.8 7.8 5.5 5.5 0 0 1 7.8-7.8Zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4',
    ];

    // Tautan: pakai route asli jika sudah ada, kalau belum arahkan ke halaman "modul"
    $to = function (string $route, string $slug) {
        if (Route::has($route)) return route($route);
        return Route::has('panel.module') ? route('panel.module', $slug) : '#';
    };

    // Pilih ikon berdasarkan nama menu (menggantikan titik)
    $iconFor = function (string $label) {
        $l = Str::lower($label);
        return match (true) {
            str_contains($l, 'dashboard')                                   => 'home',
            str_contains($l, 'anak') || str_contains($l, 'balita')          => 'child',
            str_contains($l, 'ibu') || str_contains($l, 'hamil')            => 'user',
            str_contains($l, 'jadwal')                                      => 'calendar',
            str_contains($l, 'konsultasi')                                  => 'chat',
            str_contains($l, 'edukasi')                                     => 'book',
            str_contains($l, 'notifikasi')                                  => 'bell',
            str_contains($l, 'role')                                        => 'shield',
            str_contains($l, 'permission')                                  => 'key',
            str_contains($l, 'admin')                                       => 'users',
            str_contains($l, 'posyandu') || str_contains($l, 'wilayah')     => 'building',
            default                                                         => 'report',
        };
    };

    // ===== MENU PER ROLE =====
    $menu = [];

    foreach (\App\Support\Navigation::for($user) as $section) {
        $heading = trim($section['heading'] ?? '');
        if (preg_match('/akun|profil/i', $heading)) continue;

        // Menu "Akses & Hak" tidak ditampilkan untuk Admin
        if ($roleKey === 'admin' && preg_match('/akses|hak/i', $heading)) continue;

        $items = [];
        foreach ($section['items'] as $item) {
            $label = trim($item['label']);
            if (preg_match('/profil|akun/i', $label)) continue;

            $items[] = [
                'label'  => $label,
                'icon'   => $iconFor($label),
                'href'   => Route::has($item['route']) ? route($item['route'], $item['params'] ?? []) : '#',
                'active' => \App\Support\Navigation::isActive($item),
            ];
        }
        if (! $items) continue;

        if (preg_match('/^(utama|dashboard|beranda)$/i', $heading) || $heading === '') {
            foreach ($items as $item) {
                $menu[] = $item;
            }
        } else {
            $menu[] = ['label' => $heading, 'icon' => $iconFor($heading), 'items' => $items];
        }
    }

    // ===== PENYUSUNAN ULANG SIDEBAR PER ROLE =====
    // Mengambil (dan menghapus) menu yang labelnya cocok dari semua grup / daftar menu.
    // Grup yang menjadi kosong otomatis dibuang.
    $extract = function (array &$daftar, string $pola): array {
        $ditemukan = [];
        $sisa      = [];

        foreach ($daftar as $entry) {
            if (isset($entry['items'])) {
                $tinggal = [];
                foreach ($entry['items'] as $item) {
                    if (preg_match($pola, $item['label'])) {
                        $ditemukan[] = $item;
                    } else {
                        $tinggal[] = $item;
                    }
                }
                if ($tinggal) {
                    $entry['items'] = $tinggal;
                    $sisa[] = $entry;
                }
            } elseif (preg_match($pola, $entry['label'])) {
                $ditemukan[] = $entry;
            } else {
                $sisa[] = $entry;
            }
        }

        $daftar = $sisa;
        return $ditemukan;
    };

    $roleKeyNorm = str_replace(['-', '_'], '', (string) $roleKey);

        if ($roleKeyNorm === 'superadmin') {
        // Urutan Super Admin: Dashboard → Manajemen User (dropdown) → Kelola Wilayah → Kelola Edukasi → Laporan → Akses & Hak (dropdown)
        $urutan = ['admin' => 1, 'ibu' => 2, 'kader' => 3, 'bidan' => 4];
        $kunci  = fn ($m) => $urutan[Str::lower(Str::afterLast(trim($m['label']), ' '))] ?? 9;

        $userItems = $extract($menu, '/^kelola\s+(admin|bidan|kader|ibu)\b/i');
        usort($userItems, fn ($a, $b) => $kunci($a) <=> $kunci($b));

        $aksesItems   = $extract($menu, '/^kelola\s+(role|permission)\b/i');
        $dashItems    = $extract($menu, '/^dashboard$/i');
        $laporanItems = $extract($menu, '/laporan/i');
        $wilayahItems = $extract($menu, '/^kelola\s+wilayah/i');
        $edukasiItems = $extract($menu, '/edukasi/i');

        $menu = array_merge(
            $dashItems,
            $userItems  ? [['label' => 'Manajemen User', 'icon' => 'users',  'items' => $userItems]]  : [],
            $wilayahItems,
            $edukasiItems,
            $laporanItems,
            $menu,   // menu lain yang belum diatur (jika ada) ditaruh sebelum Akses & Hak
            $aksesItems ? [['label' => 'Akses & Hak',    'icon' => 'shield', 'items' => $aksesItems]] : []
        );
    } elseif ($roleKey === 'admin') {
        // Admin: grup "Data" menjadi "Data Master"; "Laporan dan Rekapitulasi" tampil langsung (tanpa dropdown)
        $menu = collect($menu)->flatMap(function ($entry) {
            if (! isset($entry['items'])) return [$entry];

            if (preg_match('/^data(\s+master)?$/i', $entry['label'])) {
                $entry['label'] = 'Data Master';
                $entry['icon']  = 'building';
                return [$entry];
            }

            if (preg_match('/laporan|rekap/i', $entry['label'])) {
                if (count($entry['items']) === 1) {
                    return $entry['items'];
                }

                return [[
                    'label'  => $entry['label'],
                    'icon'   => $entry['icon'],
                    'href'   => $entry['items'][0]['href'],
                    'active' => collect($entry['items'])->contains(fn ($i) => ! empty($i['active'])),
                ]];
            }

            return [$entry];
        })->values()->all();
    } elseif (in_array($roleKey, ['ibu', 'orang-tua'], true)) {
        // Ibu: "Jadwal Pemeriksaan" dihapus (nanti digabung ke satu menu "Jadwal")
        $extract($menu, '/^jadwal\s+pemeriksaan$/i');
    }

    // Bidan, Kader, dan Ibu menunya langsung tampil tanpa judul grup (dropdown hanya untuk Super Admin dan Admin)
    if (in_array($roleKey, ['bidan', 'kader', 'ibu', 'orang-tua'], true)) {
        $menu = collect($menu)
            ->flatMap(fn ($entry) => isset($entry['items']) ? $entry['items'] : [$entry])
            ->values()
            ->all();
    }

    $isActive = function (array $item) {
        if (array_key_exists('active', $item)) return $item['active'];
        $href = $item['href'] ?? '#';
        if ($href === '#') return false;
        $current = url()->current();
        return !empty($item['exact'])
            ? $current === $href
            : ($current === $href || Str::startsWith($current, rtrim($href, '/') . '/'));
    };
@endphp

<body class="h-full bg-white font-sans text-slate-800 antialiased">

    {{-- ================= HEADER ================= --}}
    <header class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-[#F1D5E0] bg-white px-4 sm:px-6">
        <div class="flex items-center gap-3">
            <button type="button" id="sidebarOpen" aria-label="Buka menu"
                    class="rounded-lg p-2 text-[#C9517E] transition hover:bg-[#FCE7F0] md:hidden">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <a href="{{ $dashboardUrl }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl bg-[#FCE7F0] p-1">
                    <img src="{{ asset('images/logo-si-kia.png') }}" alt="" class="h-full w-full object-contain">
                </span>
                <span class="text-lg font-bold tracking-wide text-[#C9517E]">SI-KIA</span>
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            {{-- Lonceng notifikasi --}}
            <button type="button" aria-label="Notifikasi"
                    class="rounded-full p-2 text-[#C9517E] transition hover:bg-[#FCE7F0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9517E]">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['bell'] }}"/></svg>
            </button>

            {{-- Dropdown profil --}}
            <div class="relative" id="profileMenu">
                <button type="button" id="profileBtn" aria-haspopup="true" aria-expanded="false"
                        class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 transition hover:bg-[#FCE7F0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9517E]">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#C9517E] text-sm font-bold text-white">{{ $initial }}</span>
                    <span class="hidden text-sm font-semibold text-[#542D3D] sm:block">{{ $roleLabel ?? $user->name }}</span>
                    <svg class="h-4 w-4 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>

                <div id="profileDropdown" role="menu"
                     class="invisible absolute right-0 mt-2 w-56 origin-top-right scale-95 rounded-2xl border border-[#F1D5E0] bg-white p-1.5 opacity-0 shadow-xl transition">
                    <div class="border-b border-[#F7E6ED] px-3 py-2.5">
                        <p class="truncate text-sm font-semibold text-[#542D3D]">{{ $user->name }}</p>
                        <p class="truncate text-xs text-slate-600">{{ $user->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" role="menuitem"
                       class="mt-1 flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm font-medium text-[#542D3D] transition hover:bg-[#FCE7F0]">
                        <svg class="h-4 w-4 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['user'] }}"/></svg>
                        Profil Saya
                    </a>
                    <button type="button" role="menuitem" data-open-logout
                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-[#542D3D] transition hover:bg-[#FCE7F0]">
                        <svg class="h-4 w-4 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17l5-5-5-5M20 12H9m4 8H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7"/></svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    {{-- Overlay mobile --}}
    <div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-[#542D3D]/40 md:hidden"></div>

    {{-- ================= SIDEBAR ================= --}}
    <aside id="sidebar"
           class="fixed bottom-0 left-0 top-16 z-30 w-64 -translate-x-full overflow-y-auto border-r border-[#F1D5E0] bg-[#FCE7F0] p-4 transition-transform duration-200 md:translate-x-0">
        <nav class="space-y-1" aria-label="Menu utama">
            @foreach ($menu as $entry)
                @if (isset($entry['items']))
                    @php $groupOpen = collect($entry['items'])->contains(fn ($i) => $isActive($i)); @endphp

                    {{-- Grup menu (dropdown) --}}
                    <div data-menu-group>
                        <button type="button" data-menu-toggle aria-expanded="{{ $groupOpen ? 'true' : 'false' }}"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-[#542D3D] transition hover:bg-white/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C9517E]">
                            <svg class="h-5 w-5 shrink-0 text-[#C9517E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$entry['icon']] ?? $icons['report'] }}"/></svg>
                            <span class="flex-1">{{ $entry['label'] }}</span>
                            <svg data-menu-chevron class="h-4 w-4 shrink-0 text-[#C9517E] transition-transform duration-200 {{ $groupOpen ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>

                        <div data-menu-panel class="{{ $groupOpen ? '' : 'hidden' }} ml-5 mt-1 space-y-1 border-l border-[#E99AB7]/60 pl-3">
                            @foreach ($entry['items'] as $item)
                                @php $active = $isActive($item); @endphp
                                <a href="{{ $item['href'] }}" @if($active) aria-current="page" @endif
                                   class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium transition
                                          {{ $active ? 'bg-[#C9517E] text-white shadow-sm' : 'text-[#542D3D] hover:bg-white/80' }}">
                                    <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#C9517E]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] ?? $icons['report'] }}"/></svg>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    @php $active = $isActive($entry); @endphp
                    <a href="{{ $entry['href'] }}" @if($active) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition
                              {{ $active ? 'bg-[#C9517E] text-white shadow-sm' : 'text-[#542D3D] hover:bg-white/80' }}">
                        <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-[#C9517E]' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$entry['icon']] ?? $icons['report'] }}"/></svg>
                        {{ $entry['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
    </aside>

    {{-- ================= KONTEN ================= --}}
    <main class="min-h-screen bg-gradient-to-br from-white via-[#FCE7F0]/70 to-[#E99AB7]/25 pt-16 md:pl-64">
        <div class="p-4 sm:p-6 lg:p-8">
            @hasSection('hide_flash')
            @else
                @php
                    // ===== POPUP HASIL (berhasil / gagal) =====
                    $routeName  = request()->route()?->getName() ?? '';
                    $segments   = explode('.', $routeName);
                    $pageAction = end($segments);

                    // Nama yang tampil di judul popup gagal, mis. "Tambah Akun Bidan Gagal" / "Edit Wilayah Gagal"
                    $entities = [
                        'bidan'         => 'Akun Bidan',
                        'kader'         => 'Akun Kader',
                        'ibu'           => 'Akun Ibu',
                        'orang-tua'     => 'Akun Ibu',
                        'admins'        => 'Akun Admin',
                        'wilayah-kerja' => 'Wilayah',
                        'roles'         => 'Role',
                        'permissions'   => 'Permission',
                    ];
                    $entity     = $entities[$segments[1] ?? ''] ?? null;
                    $failVerb   = ['create' => 'Tambah', 'edit' => 'Edit'][$pageAction] ?? null;

                    $popupSuccess = session('success') ?? session('status') ?? session('message');
                    $popupError   = session('error');
                    $popupInvalid = $errors->any() && $failVerb;
                    $popupType    = ($popupError || $popupInvalid) ? 'error' : ($popupSuccess ? 'success' : null);

                    $popupTitle = $popupType === 'error'
                        ? (($failVerb && $entity) ? "{$failVerb} {$entity} Gagal" : 'Proses Gagal')
                        : $popupSuccess;
                    $popupText = $popupType === 'error'
                        ? ($popupError ?: 'Periksa kembali data yang Anda masukkan.')
                        : null;
                @endphp

                @if ($popupType)
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            SikiaSwal.fire({
                                icon: @json($popupType),
                                iconColor: @json($popupType === 'success' ? '#C9517E' : '#DC2626'),
                                title: @json($popupTitle),
                                text: @json($popupText),
                                confirmButtonText: 'Kembali'
                            });
                        });
                    </script>
                @endif
            @endif
            @yield('content')
        </div>
    </main>

    {{-- ================= MODAL KONFIRMASI LOGOUT ================= --}}
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="logoutTitle">
        <div class="absolute inset-0 bg-[#542D3D]/50" data-close-logout></div>
        <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl sm:p-8">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#FCE7F0] text-[#C9517E]">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17l5-5-5-5M20 12H9m4 8H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7"/></svg>
            </div>
            <h2 id="logoutTitle" class="text-lg font-bold text-[#542D3D]">Apakah Anda yakin ingin keluar dari akun?</h2>
            <div class="mt-6 flex gap-3">
                <button type="button" data-close-logout
                        class="flex-1 rounded-xl border-2 border-[#E99AB7] bg-white px-4 py-2.5 text-sm font-semibold text-[#C9517E] transition hover:bg-[#FCE7F0]">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-xl bg-[#C9517E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#B7436D]">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Dropdown profil
            const btn = document.getElementById('profileBtn');
            const dd = document.getElementById('profileDropdown');
            const box = document.getElementById('profileMenu');
            const setDd = (open) => {
                dd.classList.toggle('invisible', !open);
                dd.classList.toggle('opacity-0', !open);
                dd.classList.toggle('scale-95', !open);
                btn.setAttribute('aria-expanded', String(open));
            };
            btn.addEventListener('click', () => setDd(dd.classList.contains('invisible')));
            document.addEventListener('click', (e) => { if (!box.contains(e.target)) setDd(false); });

            // Modal logout
            const modal = document.getElementById('logoutModal');
            const setModal = (open) => {
                modal.classList.toggle('hidden', !open);
                modal.classList.toggle('flex', open);
            };
            document.querySelectorAll('[data-open-logout]').forEach(b => b.addEventListener('click', () => { setDd(false); setModal(true); }));
            document.querySelectorAll('[data-close-logout]').forEach(b => b.addEventListener('click', () => setModal(false)));

            // Sidebar mobile
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('sidebarOverlay');
            const setSb = (open) => {
                sb.classList.toggle('-translate-x-full', !open);
                ov.classList.toggle('hidden', !open);
            };
            document.getElementById('sidebarOpen').addEventListener('click', () => setSb(true));
            ov.addEventListener('click', () => setSb(false));
            sb.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setSb(false)));

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') { setDd(false); setModal(false); setSb(false); }
            });
        });
    </script>

    <script>
        // Dropdown grup menu sidebar (hanya muncul untuk Super Admin dan Admin)
        document.querySelectorAll('[data-menu-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const group   = btn.closest('[data-menu-group]');
                const panel   = group.querySelector('[data-menu-panel]');
                const chevron = group.querySelector('[data-menu-chevron]');
                const open    = panel.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', String(open));
                chevron.classList.toggle('rotate-180', open);
            });
        });
    </script>

    {{-- Sapaan sesuai jam pengguna. Pakai: <span data-greeting>Selamat datang</span> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const h = new Date().getHours();
            const sapaan = h < 4  ? 'Selamat malam'
                         : h < 11 ? 'Selamat pagi'
                         : h < 15 ? 'Selamat siang'
                         : h < 18 ? 'Selamat sore'
                         :          'Selamat malam';

            document.querySelectorAll('[data-greeting]').forEach(function (el) {
                el.textContent = sapaan;
            });
        });
    </script>

    <script>
        // Konfirmasi hapus: <form data-confirm-delete data-name="Nama">
        // Konfirmasi aktif/nonaktif: <form data-confirm-toggle data-active="1|0" data-name="Nama">
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || form.dataset.confirmed) return;

            if (form.hasAttribute('data-confirm-delete')) {
                e.preventDefault();
                SikiaConfirm({
                    danger: true,
                    title: form.dataset.title || ('Apakah akan menghapus akun ' + (form.dataset.name || '') + '?'),
                    text: form.dataset.text || 'Data akun akan dihapus secara permanen dan tidak dapat dipulihkan. Lanjutkan?',
                    confirmText: form.dataset.confirmText || 'Ya, Hapus Akun'
                }, function () { form.dataset.confirmed = '1'; form.submit(); });
            } else if (form.hasAttribute('data-confirm-toggle')) {
                e.preventDefault();
                const active = form.dataset.active === '1';
                SikiaConfirm({
                    title: (active ? 'Apakah akan menonaktifkan akun ' : 'Apakah akan mengaktifkan akun ') + (form.dataset.name || '') + '?',
                    text: active ? 'Akun nonaktif tidak dapat login ke sistem. Lanjutkan?' : 'Akun aktif dapat kembali login dan menggunakan sistem. Lanjutkan?',
                    confirmText: active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'
                }, function () { form.dataset.confirmed = '1'; form.submit(); });
            }
        });
    </script>
        <script>
        // Kolom pencarian: bila dikosongkan, halaman kembali ke tampilan awal (tanpa pencarian)
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type="search"][name="search"]').forEach(function (input) {
                const url = new URL(window.location.href);
                const sedangMencari = (url.searchParams.get('search') || '').trim() !== '';
                if (!sedangMencari) return;

                let sudahPindah = false;
                const kembali = function () {
                    if (sudahPindah || input.value.trim() !== '') return;
                    sudahPindah = true;
                    url.searchParams.delete('search');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                };

                input.addEventListener('input', kembali);
                input.addEventListener('search', kembali); // tombol "x" di kolom pencarian
            });
        });
    </script>
    @stack('scripts')
</body>
</html>