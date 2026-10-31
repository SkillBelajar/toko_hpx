@extends($isSuperadmin ? 'superadmin.layout' : 'tenant.layout')

@section('title', 'Panduan Pengguna')
@section('heading', 'Panduan Pengguna')

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">
            Panduan {{ $isSuperadmin ? 'Superadmin' : 'Pemilik Toko' }}
        </p>
        <h2 class="mt-1 text-lg font-bold text-slate-900">
            {{ $isSuperadmin ? 'Mengelola paket & toko yang berlangganan' : 'Menjalankan kasir, stok, dan shift toko' }}
        </h2>
        <p class="mt-1 text-sm text-slate-600">
            Versi aplikasi {{ config('app.version', '1.0 (Final)') }}. Ikuti urutan langkah di bawah.
        </p>
    </div>

    @php
        $superadminSteps = [
            [
                'title' => 'Membuat paket berlangganan',
                'steps' => [
                    'Buka menu Kelola Paket → tombol "+ Paket Baru".',
                    'Isi Nama Paket, Harga Bulanan (Rp), Limit Total Transaksi, dan Limit Total IMEI.',
                    'Kosongkan Limit bila paket unlimited (contoh: Pro).',
                    'Centang "Jadikan paket default" untuk paket yang otomatis dipakai toko baru — flag pada paket lain otomatis mati.',
                    'Simpan. Paket langsung bisa dipilih saat mengaktifkan toko.',
                ],
            ],
            [
                'title' => 'Menyetel nomor WhatsApp admin',
                'steps' => [
                    'Nomor diambil dari variabel WA_ADMIN_NUMBER di file .env (format 62xxx, tanpa + dan tanpa spasi).',
                    'Setelah mengubah, jalankan: docker restart tokohpx_app (Octane menyimpan config di memori).',
                    'Cek hasilnya di halaman Panduan ini — nomor aktif ditampilkan di bagian bawah.',
                    'Nomor ini dipakai tombol "Hubungi Admin via WhatsApp" saat tenant kena limit atau paket expired.',
                ],
            ],
            [
                'title' => 'Mengaktifkan paket tenant yang sudah membayar',
                'steps' => [
                    'Buka menu Kelola Toko.',
                    'Cari toko yang bersangkutan, klik "Edit".',
                    'Pilih Paket Aktif sesuai yang dibayar.',
                    'Isi Expired Date (tanggal berakhir) — biarkan kosong bila tanpa batas waktu.',
                    'Simpan. Tenant langsung bisa bertransaksi lagi pada login berikutnya; limit transaksi dihitung ulang dari total transaksi yang sudah ada.',
                ],
            ],
        ];

        $tenantSteps = [
            [
                'title' => 'Menambah stok & nomor IMEI',
                'steps' => [
                    'Buka menu Inventori → "+ Tambah Stok / IMEI".',
                    'Isi Tipe HP (contoh: Samsung Galaxy A15 8/256), Brand, dan Harga Jual.',
                    'Isi Tanggal Masuk — tanggal ini dipakai menghitung umur stok (Aging Alert).',
                    'Isi Nomor IMEI di kotak besar: satu IMEI per baris, atau pisahkan dengan koma/spasi.',
                    'Simpan. Baris stok yang lebih dari 30 hari dan belum laku akan ditandai merah.',
                ],
            ],
            [
                'title' => 'Memakai Camera Scanner di HP',
                'steps' => [
                    'Penting: kamera browser hanya aktif pada alamat https:// atau localhost. Membuka lewat http://IP-toko akan ditolak browser.',
                    'Buka menu Kasir → tombol besar "📷 Scan Barcode IMEI".',
                    'Izinkan akses kamera saat browser bertanya (pilih Allow / Izinkan).',
                    'Arahkan kamera belakang ke barcode di kotak atau label HP, jarak ±15–20 cm, cahaya cukup.',
                    'Bila kamera tidak tersedia, ketik IMEI di kolom manual lalu tekan Enter.',
                ],
            ],
            [
                'title' => 'Buka & tutup shift (modal laci)',
                'steps' => [
                    'Buka Shift: isi uang fisik yang ada di laci saat mulai bertugas, lalu simpan. Tanpa shift aktif, halaman Kasir tidak bisa dibuka.',
                    'Selama shift, setiap transaksi menambah catatan "uang seharusnya" secara otomatis.',
                    'Tutup Shift: hitung ulang uang di laci, masukkan angka fisiknya, lalu simpan.',
                    'Selisih = uang fisik − uang seharusnya. Selisih minus tampil merah di Laporan Shift sebagai temuan kas.',
                    'Laporan Shift: menu Laporan → "Laporan Shift".',
                ],
            ],
            [
                'title' => 'Upgrade paket',
                'steps' => [
                    'Saat limit transaksi habis atau masa aktif berakhir, checkout otomatis ditolak.',
                    'Di layar kasir muncul pesan penyebab + tombol "Hubungi Admin via WhatsApp".',
                    'Tombol tersebut sudah berisi pesan siap kirim: "Halo Admin, saya ingin Upgrade ke Paket …".',
                    'Setelah pembayaran, admin mengubah paket dan tanggal aktif dari sisi Superadmin.',
                    'Cek sisa kuota kapan saja di halaman Inventori (indikator IMEI) dan kartu "Transaksi terpakai" di Kasir.',
                ],
            ],
        ];

        $sections = $isSuperadmin ? $superadminSteps : $tenantSteps;
    @endphp

    <div class="mt-4 space-y-3">
        @foreach ($sections as $i => $section)
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" class="flex w-full items-center gap-3 px-5 py-4 text-left">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                        {{ $i + 1 }}
                    </span>
                    <span class="flex-1 text-sm font-bold text-slate-900">{{ $section['title'] }}</span>
                    <span class="text-xs font-semibold text-indigo-600" x-text="open ? 'Tutup' : 'Buka'">Buka</span>
                </button>

                <div x-cloak x-show="open" class="border-t border-slate-100 px-5 py-4">
                    <ol class="space-y-2">
                        @foreach ($section['steps'] as $step)
                            <li class="flex gap-2 text-sm leading-relaxed text-slate-600">
                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                                <span>{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kontak admin (WhatsApp)</p>
        <p class="mt-1 font-mono text-sm font-bold text-slate-900">{{ $waAdmin }}</p>
        <p class="mt-1 text-xs text-slate-500">
            Ubah lewat WA_ADMIN_NUMBER di .env, lalu restart container app.
        </p>
    </div>

</div>
@endsection
