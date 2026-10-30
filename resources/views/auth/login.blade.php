@extends('layouts.auth')

@section('title', 'Masuk')
@section('heading', 'Masuk ke akun Anda')
@section('subheading', 'Kelola kasir dan stok toko HP dari satu tempat.')

@section('form')
<form method="POST" action="{{ route('login.store') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="nama@email.com">
    </div>

    <div x-data="{ show: false }">
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <div class="relative mt-1">
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                class="block w-full rounded-xl border border-slate-300 px-4 py-3 pr-16 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                placeholder="••••••••">
            <button type="button" @click="show = !show"
                class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800"
                x-text="show ? 'Sembunyi' : 'Lihat'">Lihat</button>
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        Ingat saya
    </label>

    <button type="submit"
        class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-base font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
        Masuk
    </button>
</form>
@endsection

@section('footer-link')
    Belum punya akun?
    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Daftar gratis</a>
@endsection
