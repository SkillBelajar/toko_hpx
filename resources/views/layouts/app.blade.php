<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Toko HP'))</title>
    <meta name="description" content="@yield('description', 'SaaS POS untuk toko HP: kasir cepat, kelola stok & IMEI, anti kecurangan kasir.')">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800 font-sans antialiased">

    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="/" class="flex items-center gap-2 font-bold text-slate-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 text-white">TH</span>
                <span class="text-lg">Toko HP</span>
            </a>

            <nav x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="sm:hidden rounded-lg border border-slate-200 p-2" aria-label="Menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div x-cloak x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-2 shadow-lg sm:hidden">
                    <a href="#fitur" class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-50">Fitur</a>
                    <a href="#harga" class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-50">Harga</a>
                    @auth
                        <a href="{{ auth()->user()->homePath() }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-50">Masuk</a>
                        <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">Daftar Gratis</a>
                    @endauth
                </div>
                <div class="hidden items-center gap-6 sm:flex">
                    <a href="#fitur" class="text-sm font-medium text-slate-600 hover:text-slate-900">Fitur</a>
                    <a href="#harga" class="text-sm font-medium text-slate-600 hover:text-slate-900">Harga</a>
                    @auth
                        <a href="{{ auth()->user()->homePath() }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Masuk</a>
                        <a href="{{ route('register') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Daftar Gratis</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Toko HP') }}. Semua hak dilindungi.</p>
            <p class="font-bold text-slate-800">Versi {{ config('app.version', '1.0 (Final)') }}</p>
        </div>
    </footer>

</body>
</html>
