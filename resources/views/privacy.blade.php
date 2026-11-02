<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi — {{ config('app.name', 'Toko HP') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#4f46e5">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white font-sans text-slate-800 antialiased">

<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur">
    <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="/" class="flex items-center gap-2 font-bold text-slate-900">
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-indigo-600 text-white">TH</span>
            <span class="text-lg">Toko HP</span>
        </a>
        <a href="/" class="text-sm font-semibold text-indigo-600 hover:underline">&larr; Beranda</a>
    </div>
</header>

<main class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kebijakan Privasi &amp; Perlindungan Data</h1>
    <p class="mt-2 text-sm text-slate-500">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

    <p class="mt-6 text-sm leading-relaxed text-slate-600">
        Kebijakan ini disusun sesuai <strong>Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (PDP)</strong>
        dan menjelaskan data apa yang kami kumpulkan, untuk apa dipakai, serta hak Anda sebagai pemilik data.
    </p>

    <h2 class="mt-8 text-lg font-bold text-slate-900">1. Data yang kami kumpulkan</h2>
    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-600">
        <li><strong>Data akun</strong> — nama toko, alamat, nomor telepon/WhatsApp, email, dan kata sandi (disimpan dalam bentuk hash satu arah, tidak pernah dibaca administrator).</li>
        <li><strong>Data transaksi</strong> — nomor IMEI unit, tipe HP, harga jual, diskon, metode pembayaran, dan waktu transaksi.</li>
        <li><strong>Data operasional</strong> — catatan audit perubahan stok (siapa menambah, mengubah, menghapus, atau menjual unit) beserta alamat IP.</li>
    </ul>

    <h2 class="mt-8 text-lg font-bold text-slate-900">2. Tujuan pemrosesan</h2>
    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-600">
        <li>Menjalankan fitur kasir, inventori, dan laporan keuangan toko Anda.</li>
        <li>Mencegah kecurangan kas: setiap perubahan stok tercatat siapa pelakunya.</li>
        <li>Menghitung batas pemakaian sesuai paket langganan yang aktif.</li>
        <li>Menghubungi Anda saat limit paket tercapai atau masa aktif berakhir.</li>
    </ul>

    <h2 class="mt-8 text-lg font-bold text-slate-900">3. Data yang TIDAK kami jual</h2>
    <p class="mt-2 text-sm leading-relaxed text-slate-600">
        Kami tidak menjual, menyewakan, atau mempertukarkan data pribadi Anda kepada pihak ketiga untuk tujuan pemasaran.
        Data hanya dipakai untuk menjalankan layanan ini sendiri.
    </p>

    <h2 class="mt-8 text-lg font-bold text-slate-900">4. Hak Anda sebagai pemilik data (Pasal 6–11 UU 27/2022)</h2>
    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-relaxed text-slate-600">
        <li><strong>Hak memperbaiki</strong> — ubah data toko dan stok kapan saja dari menu Pengaturan dan Inventori.</li>
        <li><strong>Hak menghapus</strong> — minta penghapusan seluruh data toko; kami proses maksimal 7 hari kerja.</li>
        <li><strong>Hak menarik persetujuan</strong> — berhenti berlangganan kapan pun; data transaksi historis tetap tersimpan sesuai kewajiban pembukuan.</li>
        <li><strong>Hak mengakses</strong> — minta salinan data yang kami simpan tentang toko Anda.</li>
    </ul>

    <h2 class="mt-8 text-lg font-bold text-slate-900">5. Keamanan</h2>
    <p class="mt-2 text-sm leading-relaxed text-slate-600">
        Kata sandi disimpan sebagai hash, akses kasir dibatasi per toko (setiap tenant hanya bisa melihat datanya sendiri),
        dan semua perubahan data penting dicatat dalam log audit yang tidak bisa diubah pengguna.
    </p>

    <h2 class="mt-8 text-lg font-bold text-slate-900">6. Kontak</h2>
    <p class="mt-2 text-sm leading-relaxed text-slate-600">
        Pertanyaan tentang data pribadi Anda dapat dikirim ke
        <a href="https://wa.me/6289696380422" class="font-semibold text-emerald-600 hover:underline">WhatsApp admin</a>.
    </p>
</main>

<footer class="border-t border-slate-200 bg-slate-50">
    <div class="mx-auto max-w-3xl px-4 py-6 text-xs text-slate-500 sm:px-6">
        &copy; {{ date('Y') }} {{ config('app.name', 'Toko HP') }}. Semua hak dilindungi.
    </div>
</footer>

</body>
</html>
