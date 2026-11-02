<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Toko HP') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100 font-sans text-slate-800 antialiased" x-data="{ sidebar: false }">

<div class="lg:flex">

    <div x-cloak x-show="sidebar" @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    <aside x-cloak
        :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64 transform bg-slate-900 text-slate-300 transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">

        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-5">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 font-bold text-white">TH</span>
            <div class="min-w-0 leading-tight">
                <p class="truncate text-sm font-bold text-white">{{ auth()->user()->tenant?->nama_toko ?? 'Toko HP' }}</p>
                <p class="text-[11px] uppercase tracking-wider text-slate-400">Tenant</p>
            </div>
        </div>

        <nav class="space-y-1 p-3 text-sm">
            @php
                $items = [
                    ['label' => 'Dashboard', 'route' => 'tenant.dashboard', 'active' => request()->routeIs('tenant.dashboard')],
                    ['label' => 'Inventori', 'route' => 'tenant.inventory.index', 'active' => request()->routeIs('tenant.inventory.*')],
                    ['label' => 'Kasir', 'route' => 'tenant.pos.index', 'active' => request()->routeIs('tenant.pos.*')],
                    ['label' => 'Laporan', 'route' => 'tenant.reports', 'active' => request()->routeIs('tenant.reports', 'tenant.reports.shifts')],
                    ['label' => 'Panduan', 'route' => 'tenant.guide', 'active' => request()->routeIs('tenant.guide')],
                    ['label' => 'Pengaturan', 'route' => 'tenant.settings', 'active' => request()->routeIs('tenant.settings')],
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
            @php $activeShift = auth()->user()->activeShift(); @endphp
            @if ($activeShift)
                <div class="mb-2 rounded-xl bg-emerald-500/10 px-3 py-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-400">Shift Aktif</p>
                    <p class="text-xs text-slate-300">Modal Rp {{ number_format((float) $activeShift->opening_amount, 0, ',', '.') }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">Sejak {{ $activeShift->opened_at->format('d M H:i') }}</p>
                </div>
                <a href="{{ route('tenant.shift.close') }}"
                    class="mb-2 block w-full rounded-xl bg-red-600 px-3 py-2 text-center text-xs font-bold text-white hover:bg-red-700">
                    Tutup Shift
                </a>
            @else
                <a href="{{ route('tenant.shift.open') }}"
                    class="mb-2 block w-full rounded-xl bg-emerald-600 px-3 py-2 text-center text-xs font-bold text-white hover:bg-emerald-700">
                    Buka Shift
                </a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl border border-white/15 px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-white/5">
                    Keluar
                </button>
            </form>
            <p class="mt-3 px-1 text-[11px] text-slate-500">Versi {{ config('app.version', '1.0 (Final)') }}</p>
        </div>
    </aside>

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
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'Toko HP') }}</span> · <a href="https://wa.me/6289696380422" class="font-semibold text-emerald-600 hover:underline">WA: 0896 9638 0422</a>
                <span class="font-bold text-slate-800">Versi {{ config('app.version', '1.0 (Final)') }}</span>
            </div>
        </footer>
    </div>
</div>

{{--
    Helper format rupiah untuk input uang.

    Kenapa: input type="number" tidak bisa menampilkan titik pemisah ribuan,
    sehingga 1000000 terbaca "1000000" dan mudah salah baca jadi 100.000.

    Cara pakai:
        x-data="money(1500000)"           -> state 'display' bertitik
        <input x-model="display" @input="display = fmt(display)" ...>
    Nilai yang dikirim ke server adalah angka bersih (tanpa titik).
--}}
{{-- Nomor bersih dari input uang bertitik. Blade: (int) "1.000.000" = 1 (PHP
     berhenti di titik), jadi harus dibersihkan dulu sebelum diformat ulang. --}}
@php
    if (! function_exists('moneyInt')) {
        function moneyInt($v): int {
            return (int) preg_replace('/\D/', '', (string) ($v ?? 0));
        }
    }
@endphp

<script>
    window.moneyFmt = function (n) {
        return Number(String(n).replace(/\D/g, '') || 0).toLocaleString('id-ID');
    };
    window.money = function (initial = 0) {
        return {
            display: window.moneyFmt(initial),
            raw: Number(String(initial).replace(/\D/g, '') || 0),
            fmt(v) {
                const clean = String(v ?? '').replace(/\D/g, '');
                this.raw = clean ? Number(clean) : 0;
                this.display = this.raw.toLocaleString('id-ID');
                return this.display;
            }
        };
    };
    // Dipakai tombol preset (mis. modal laci cepat 100.000).
    window.moneySet = function (id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = Number(value).toLocaleString('id-ID');
        el.dispatchEvent(new Event('input', { bubbles: true }));
    };
</script>

</body>
</html>
