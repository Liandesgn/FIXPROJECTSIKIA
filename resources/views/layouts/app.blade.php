<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — SI-KIA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 font-sans text-slate-800">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 flex-col bg-slate-900 text-slate-100 md:flex">
            <div class="flex h-16 items-center border-b border-white/10 px-6">
                <span class="text-lg font-bold tracking-wide">SI-KIA</span>
            </div>

            @php
                $navItem = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition';
                $navActive = 'bg-sky-600 text-white';
                $navIdle = 'text-slate-300 hover:bg-white/5 hover:text-white';
                $navigation = \App\Support\Navigation::for(auth()->user());
            @endphp

            <nav class="flex-1 space-y-4 overflow-y-auto p-4">
                @foreach ($navigation as $section)
                    <div>
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            {{ $section['heading'] }}
                        </p>
                        <div class="space-y-1">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route'], $item['params']) }}"
                                   class="{{ $navItem }} {{ \App\Support\Navigation::isActive($item) ? $navActive : $navIdle }}">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                <div class="text-xs text-slate-400">{{ auth()->user()->primaryRole()?->label() }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-lg bg-white/10 px-3 py-2 text-sm font-medium transition hover:bg-white/20">
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6">
                <h1 class="text-lg font-semibold text-slate-800">@yield('title', 'Dashboard')</h1>
                <div class="flex items-center gap-4">
                    <span class="hidden text-sm text-slate-500 sm:block">{{ now()->format('d M Y') }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="md:hidden">
                        @csrf
                        <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white">
                            Keluar
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
