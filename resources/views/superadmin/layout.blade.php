<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Superadmin') — {{ config('app.name', 'TokoHPX') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-800 antialiased" x-data="{ sidebar: false }">

<div class="lg:flex">

    {{-- Overlay mobile --}}
    <div x-cloak x-show="sidebar" @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside x-cloak
        :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-300 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">

        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-5">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 font-bold text-white">TH</span>
            <div class="leading-tight">
                <p class="text-sm font-bold text-white">TokoHPX</p>
                <p class="text-[11px] uppercase tracking-wider text-slate-400">Superadmin</p>
            </div>
        </div>

        <nav class="space-y-1 p-3 text-sm">
            @php
                $items = [
                    ['label' => 'Dashboard', 'route' => 'superadmin.dashboard', 'active' => request()->routeIs('superadmin.dashboard')],
                    ['label' => 'Kelola Paket', 'route' => 'superadmin.packages.index', 'active' => request()->routeIs('superadmin.packages.*')],
                    ['label' => 'Kelola Toko', 'route' => 'superadmin.tenants.index', 'active' => request()->routeIs('superadmin.tenants.*')],
                    ['label' => 'Pengaturan', 'route' => 'superadmin.settings', 'active' => request()->routeIs('superadmin.settings')],
                ];
            @endphp

            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}"
                    class="flex items-center justify-between rounded-xl px-3 py-2.5 font-medium transition
                        {{ $item['active'] ? 'bg-indigo-600 text-white shadow-sm' : 'hover:bg-white/5 hover:text-white' }}">
                    {{ $item['label'] }}
                    @if ($item['active'])
                        <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="absolute bottom-0 left-0 right-0 border-t border-white/10 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-white/15 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-white/5">
                    Keluar
                </button>
            </form>
            <p class="mt-3 px-1 text-[11px] text-slate-500">Versi {{ config('app.version', '0.19') }}</p>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex min-h-screen w-full flex-col lg:min-w-0">

        <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebar = true" class="rounded-lg border border-slate-300 p-2 lg:hidden" aria-label="Menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-sm font-bold text-slate-900 sm:text-base">@yield('heading', 'Dashboard')</h1>
            </div>
            <span class="max-w-[10rem] truncate text-sm text-slate-600">{{ auth()->user()->name }}</span>
        </header>

        @foreach (['status' => 'emerald', 'error' => 'red'] as $key => $color)
            @if (session($key))
                <div class="px-4 pt-4 sm:px-6">
                    <div class="rounded-xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-4 py-3 text-sm text-{{ $color }}-700">
                        {{ session($key) }}
                    </div>
                </div>
            @endif
        @endforeach

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white px-4 py-4 text-xs text-slate-500 sm:px-6">
            <div class="flex items-center justify-between">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'TokoHPX') }}</span>
                <span class="font-bold text-slate-800">Versi {{ config('app.version', '0.19') }}</span>
            </div>
        </footer>
    </div>
</div>

</body>
</html>
