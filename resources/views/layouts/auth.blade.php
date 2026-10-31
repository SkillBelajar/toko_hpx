<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') — {{ config('app.name', 'TokoHPX') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 font-sans text-slate-800 antialiased">

    <div class="flex flex-1 items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            <div class="mb-6 flex flex-col items-center text-center">
                <a href="/" class="flex items-center gap-2 text-lg font-bold text-slate-900">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-600 text-white">TH</span>
                    <span>TokoHP<span class="text-indigo-600">X</span></span>
                </a>
                <h1 class="mt-5 text-2xl font-bold tracking-tight text-slate-900">@yield('heading')</h1>
                <p class="mt-2 text-sm text-slate-500">@yield('subheading')</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                @if (session('error'))
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('form')
            </div>

            <p class="mt-5 text-center text-sm text-slate-500">@yield('footer-link')</p>
        </div>
    </div>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-md items-center justify-between px-4 py-4 text-xs text-slate-500 sm:px-6">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'TokoHPX') }}</span>
            <span class="font-bold text-slate-800">Versi {{ config('app.version', '0.19') }}</span>
        </div>
    </footer>

</body>
</html>
