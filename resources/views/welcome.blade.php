@extends('layouts.app')

@section('title', 'Toko HP — Sistem Kasir & Kelola Stok Toko HP')
@section('description', 'SaaS POS untuk toko HP: scan IMEI pakai kamera HP, anti kecurangan kasir dengan shift, dan notifikasi stok mengendap.')

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-b from-indigo-50 via-white to-white">
    <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-indigo-200/50 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-sky-200/50 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-24">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white px-3 py-1 text-xs font-semibold text-indigo-700">
                <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                Multi-tenant SaaS untuk toko HP
            </span>

            <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                Sistem Kasir &amp; Kelola Stok Toko HP<br>
                <span class="bg-gradient-to-r from-indigo-600 to-sky-500 bg-clip-text text-transparent">Cukup Pakai Kamera HP Anda</span>
            </h1>

            <p class="mt-3 text-sm font-semibold text-indigo-700">
                &#128222; tokohp.pawandigital.id
            </p>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                Catat penjualan HP dalam hitungan detik: scan IMEI langsung dari kamera, stok terpotong otomatis,
                dan laporan kas shift yang tidak bisa dimanipulasi kasir.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="rounded-xl bg-indigo-600 px-6 py-3 text-center text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">
                    Daftar Gratis
                </a>
                <a href="#fitur" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Lihat Fitur
                </a>
            </div>

            <p class="mt-4 text-xs text-slate-500">Tanpa kartu kredit. Paket Free langsung aktif.</p>
            <p class="mt-3 text-xs text-slate-500">WhatsApp Admin: <a href="https://wa.me/6289696380422" class="font-semibold text-emerald-600 hover:underline">0896 9638 0422</a></p>
        </div>

        <div class="relative mx-auto w-full max-w-sm">
            <div class="rounded-[2rem] border border-slate-200 bg-white p-4 shadow-2xl shadow-indigo-900/10">
                <div class="rounded-[1.5rem] bg-slate-900 p-4 text-white">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>Kasir &middot; Shift Pagi</span>
                        <span class="rounded-full bg-emerald-500/20 px-2 py-0.5 font-semibold text-emerald-300">OPEN</span>
                    </div>
                    <div class="mt-4 rounded-xl bg-white/5 p-4">
                        <p class="text-xs text-slate-400">Total penjualan hari ini</p>
                        <p class="mt-1 text-2xl font-bold tracking-tight">Rp 4.750.000</p>
                        <p class="mt-1 text-xs text-emerald-400">+3 transaksi &middot; 3 IMEI terdaftar</p>
                    </div>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-2 text-xs">
                            <span class="text-slate-300">Scan IMEI</span>
                            <span class="font-mono text-indigo-300">35&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;1</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-white/5 px-3 py-2 text-xs">
                            <span class="text-slate-300">Stok mengendap</span>
                            <span class="font-semibold text-amber-300">7 unit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FITUR --}}
<section id="fitur" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
    <div class="max-w-2xl">
        <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Fitur Utama</p>
        <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
            Dibangun untuk masalah nyata toko HP
        </h2>
        <p class="mt-4 text-slate-600">
            Bukan sekadar aplikasi kasir. Tiga fitur ini menutup celah kebocoran uang yang paling sering terjadi.
        </p>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="grid h-11 w-11 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v6c0 4.4-3 7.6-7 9-4-1.4-7-4.6-7-9V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 12l1.8 1.8L15 10"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">Anti-Kecurangan Kasir</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Sistem buka/tutup shift dengan modal laci. Setiap selisih uang fisik vs sistem tercatat per kasir,
                bukan per toko &mdash; jadi siapa yang bertanggung jawab selalu jelas.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-2.1A2 2 0 0118 13.8V10a6 6 0 10-12 0v3.8c0 .4-.1.8-.4 1.1L4 17h5"/><path stroke-linecap="round" d="M10 21h4"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">Notifikasi Stok Mengendap</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                HP yang lebih dari X hari tidak laku otomatis ditandai. Uang tidak mati di rak: sistem
                mendorong Anda menurunkan harga atau memindah unit yang paling cepat habis.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:col-span-2 lg:col-span-1">
            <div class="grid h-11 w-11 place-items-center rounded-xl bg-sky-50 text-sky-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 012-2h2M20 8V6a2 2 0 00-2-2h-2M4 16v2a2 2 0 002 2h2M20 16v2a2 2 0 01-2 2h-2"/><path stroke-linecap="round" d="M4 12h16"/></svg>
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">Scan IMEI via Web</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Tidak perlu alat tambahan. Kamera HP atau laptop dipakai untuk membaca barcode IMEI,
                langsung tersimpan rapi tanpa salah ketik 15 digit.
            </p>
        </div>
    </div>
</section>

{{-- HARGA --}}
<section id="harga" class="border-y border-slate-200 bg-slate-50">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
        <div class="max-w-2xl">
            <p class="text-sm font-bold uppercase tracking-wider text-indigo-600">Harga</p>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Mulai gratis, naik saat toko Anda tumbuh</h2>
            <p class="mt-4 text-slate-600">
                Harga flat per bulan, tanpa biaya per transaksi. Kuota transaksi dan stok bersifat
                <strong class="text-slate-900">akumulasi total</strong> &mdash; bukan per bulan.
            </p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2">
            <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h3 class="text-lg font-bold text-slate-900">Free</h3>
                <p class="mt-1 text-sm text-slate-500">Untuk toko yang baru mulai.</p>
                <p class="mt-6 flex items-baseline gap-1">
                    <span class="text-4xl font-extrabold tracking-tight text-slate-900">Rp 0</span>
                    <span class="text-sm text-slate-500">/bulan</span>
                </p>
                <ul class="mt-6 space-y-3 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Limit Total Transaksi: <strong class="text-slate-900">30</strong></li>
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Limit Stok: <strong class="text-slate-900">50</strong> unit</li>
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Scan IMEI, shift kasir, laporan dasar</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Mulai Gratis</a>
            </div>

            <div class="relative flex flex-col rounded-2xl border-2 border-indigo-600 bg-white p-6 shadow-xl shadow-indigo-600/10 sm:p-8">
                <span class="absolute -top-3 right-6 rounded-full bg-indigo-600 px-3 py-1 text-xs font-bold text-white">Paling Populer</span>
                <h3 class="text-lg font-bold text-slate-900">Pro</h3>
                <p class="mt-1 text-sm text-slate-500">Untuk toko yang sudah jalan harian.</p>
                <p class="mt-6 flex items-baseline gap-1">
                    <span class="text-4xl font-extrabold tracking-tight text-slate-900">Rp 90.000</span>
                    <span class="text-sm text-slate-500">/bulan</span>
                </p>
                <ul class="mt-6 space-y-3 text-sm text-slate-600">
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Transaksi: <strong class="text-slate-900">Unlimited</strong></li>
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Stok: <strong class="text-slate-900">Unlimited</strong></li>
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Peringatan stok mengendap &amp; pengingat upsell</li>
                    <li class="flex gap-2"><span class="text-indigo-600">&#10003;</span> Multi-kasir dengan rekap selisih per shift</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 rounded-xl bg-indigo-600 px-4 py-3 text-center text-sm font-bold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-700">Pilih Pro</a>
            </div>
        </div>

        <p class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <strong>Penting soal kuota:</strong> limit transaksi dan stok adalah akumulasi total (seumur hidup akun),
            bukan reset tiap bulan. Paket Free cocok untuk uji coba; setelah total 30 transaksi tercapai,
            akun perlu upgrade ke Pro untuk melanjutkan.
        </p>
    </div>
</section>

{{-- CTA --}}
<section id="daftar" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-24">
    <div class="rounded-3xl bg-gradient-to-br from-indigo-600 to-sky-600 px-6 py-12 text-center shadow-2xl shadow-indigo-600/20 sm:px-12">
        <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">Siap rapikan kasir &amp; stok toko HP Anda?</h2>
        <p class="mx-auto mt-3 max-w-xl text-indigo-100">Daftar gratis hari ini, catat transaksi pertama dalam 5 menit.</p>
        <a href="{{ route('register') }}" class="mt-7 inline-block rounded-xl bg-white px-7 py-3 text-sm font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50">
            Daftar Gratis
        </a>
    </div>
</section>

@endsection
