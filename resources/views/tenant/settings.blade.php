@extends('tenant.layout')

@section('title', 'Pengaturan')
@section('heading', 'Pengaturan')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Toko & Akun</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola informasi toko untuk nota kasir, profil akun pengguna, dan keamanan.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 border border-indigo-200 px-3 py-1 text-xs font-bold text-indigo-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Toko: {{ $tenant?->nama_toko ?? '—' }}
            </span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- KOLOM KIRI: INFORMASI TOKO & PAKET --}}
        <div class="space-y-6">

            {{-- KARTU 1: INFORMASI TOKO --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-50 text-indigo-600 font-bold text-sm">🏪</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Informasi Toko</h2>
                        <p class="text-[11px] text-slate-500">Tampil pada kepala nota, struk kasir, dan laporan ekspor</p>
                    </div>
                </div>

                @if (session('status_store'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800">
                        ✅ {{ session('status_store') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('tenant.settings.store') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="nama_toko" class="block text-xs font-semibold text-slate-700">
                            Nama Toko <span class="text-red-500">*</span>
                        </label>
                        <input id="nama_toko" name="nama_toko" type="text" required
                            value="{{ old('nama_toko', $tenant?->nama_toko) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="Contoh: Dua Ponsel Ketapang">
                        @error('nama_toko') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="no_telp" class="block text-xs font-semibold text-slate-700">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input id="no_telp" name="no_telp" type="text"
                            value="{{ old('no_telp', $tenant?->no_telp) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="Contoh: 081234567890">
                        <p class="mt-1 text-[11px] text-slate-400">Nomor kontak layanan yang tertera di struk nota untuk pelanggan.</p>
                        @error('no_telp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="alamat" class="block text-xs font-semibold text-slate-700">
                            Alamat Toko
                        </label>
                        <textarea id="alamat" name="alamat" rows="2"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="Contoh: Jl. R. Suprapto No. 88, Ketapang, Kalimantan Barat">{{ old('alamat', $tenant?->alamat) }}</textarea>
                        @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 transition active:scale-[0.99]">
                            Simpan Informasi Toko
                        </button>
                    </div>
                </form>
            </div>

            {{-- KARTU 2: PAKET & KUOTA AKTIF --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-xl bg-emerald-50 text-emerald-600 font-bold text-sm">📦</span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Paket & Langganan</h2>
                            <p class="text-[11px] text-slate-500">Kapasitas kuota IMEI dan transaksi sistem</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                        {{ $tenant?->package?->name ?? 'Standar' }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <span class="text-slate-500 block text-[11px]">Total IMEI Terdaftar</span>
                        <strong class="text-slate-900 text-base font-extrabold">{{ $tenant?->imeiUsed() ?? 0 }}</strong>
                        <span class="text-slate-400 text-[10px]"> / {{ $tenant?->imeiLimit() ?? '∞ Unlimited' }}</span>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3">
                        <span class="text-slate-500 block text-[11px]">Total Transaksi</span>
                        <strong class="text-slate-900 text-base font-extrabold">{{ $tenant?->transactionUsed() ?? 0 }}</strong>
                        <span class="text-slate-400 text-[10px]"> / {{ $tenant?->transactionLimit() ?? '∞ Unlimited' }}</span>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between text-xs text-slate-500 border-t border-slate-100 pt-3">
                    <span>Masa Aktif:</span>
                    <strong class="text-slate-800">
                        @if ($tenant?->expired_date)
                            Sampai {{ $tenant->expired_date->format('d M Y') }}
                        @else
                            Aktif Permanen / Tanpa Kedaluwarsa
                        @endif
                    </strong>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: PROFIL PENGGUNA & GANTI PASSWORD --}}
        <div class="space-y-6">

            {{-- KARTU 3: PROFIL PENGGUNA --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-blue-50 text-blue-600 font-bold text-sm">👤</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Profil Pengguna</h2>
                        <p class="text-[11px] text-slate-500">Nama kasir/admin dan alamat email login</p>
                    </div>
                </div>

                @if (session('status_profile'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800">
                        ✅ {{ session('status_profile') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('tenant.settings.profile') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700">
                            Nama Pengguna <span class="text-red-500">*</span>
                        </label>
                        <input id="name" name="name" type="text" required
                            value="{{ old('name', $user->name) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700">
                            Alamat Email (Login) <span class="text-red-500">*</span>
                        </label>
                        <input id="email" name="email" type="email" required
                            value="{{ old('email', $user->email) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition active:scale-[0.99]">
                            Simpan Profil Pengguna
                        </button>
                    </div>
                </form>
            </div>

            {{-- KARTU 4: GANTI PASSWORD --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-amber-50 text-amber-600 font-bold text-sm">🔒</span>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Ubah Kata Sandi</h2>
                        <p class="text-[11px] text-slate-500">Perbarui kata sandi akun untuk menjaga keamanan toko</p>
                    </div>
                </div>

                @if (session('status_password'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800">
                        ✅ {{ session('status_password') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('tenant.settings.password') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-700">
                            Password Saat Ini <span class="text-red-500">*</span>
                        </label>
                        <input id="current_password" name="current_password" type="password" required
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="••••••••">
                        @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input id="password" name="password" type="password" required
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                placeholder="Min. 6 karakter">
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700">
                                Ulangi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                                placeholder="Ulangi password">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition active:scale-[0.99]">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
