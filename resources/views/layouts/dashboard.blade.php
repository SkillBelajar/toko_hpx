<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'Toko HP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 font-sans text-slate-800 antialiased">

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <a href="/" class="flex items-center gap-2 font-bold text-slate-900">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 text-white">TH</span>
                <span class="hidden sm:inline">Toko HP</span>
            </a>

            <div class="flex items-center gap-3 text-sm">
                <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 sm:inline">
                    @yield('badge', auth()->user()->role)
                </span>
                <span class="max-w-[9rem] truncate text-slate-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    @if (session('error'))
        <div class="mx-auto mt-4 w-full max-w-5xl px-4 sm:px-6">
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        </div>
    @endif

    <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8 sm:px-6">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 text-xs text-slate-500 sm:px-6">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'Toko HP') }}</span> · <a href="https://wa.me/6289696380422" class="font-semibold text-emerald-600 hover:underline">WA: 0896 9638 0422</a>
            <span class="font-bold text-slate-800">Versi {{ config('app.version', '1.0 (Final)') }}</span>
        </div>
    </footer>

</body>
</html>
