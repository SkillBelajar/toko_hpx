@extends('layouts.auth')

@section('title', 'Daftar')
@section('heading', 'Buat akun toko Anda')
@section('subheading', 'Gratis, tanpa kartu kredit. Langsung bisa dipakai.')

@section('form')
<form method="POST" action="{{ route('register.store') }}" class="space-y-4">
    @csrf

    <div>
        <label for="nama_toko" class="block text-sm font-medium text-slate-700">Nama Toko</label>
        <input id="nama_toko" name="nama_toko" type="text" value="{{ old('nama_toko') }}" required autofocus
            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="Mis. Toko HP Berkah Jaya">
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama Anda</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="Nama lengkap / pemilik">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="nama@email.com">
    </div>

    <div>
        <label for="no_telp" class="block text-sm font-medium text-slate-700">No. Telp <span class="font-normal text-slate-400">(opsional)</span></label>
        <input id="no_telp" name="no_telp" type="tel" inputmode="tel" value="{{ old('no_telp') }}" autocomplete="tel"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="08xxxxxxxxxx">
    </div>

    <div x-data="{ show: false }">
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <div class="relative mt-1">
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password" minlength="8"
                class="block w-full rounded-xl border border-slate-300 px-4 py-3 pr-16 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                placeholder="Minimal 8 karakter">
            <button type="button" @click="show = !show"
                class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-slate-500 hover:text-slate-800"
                x-text="show ? 'Sembunyi' : 'Lihat'">Lihat</button>
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Ulangi Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-base text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="Ulangi password">
    </div>

    <button type="submit"
        class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-base font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
        Daftar Gratis
    </button>
</form>
@endsection

@section('footer-link')
    Sudah punya akun?
    <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Masuk di sini</a>
@endsection
