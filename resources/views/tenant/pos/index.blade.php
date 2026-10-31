@extends('tenant.layout')

@section('title', 'Kasir')
@section('heading', 'Kasir')

@section('content')
<div class="mx-auto max-w-lg" x-data="posScanner()" x-init="init()">

    {{-- Info shift --}}
    <div class="mb-4 flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Shift aktif</p>
            <p class="truncate text-sm text-emerald-900">
                Kasir {{ $shift?->user?->name }} &middot; modal Rp {{ number_format((float) ($shift->opening_amount ?? 0), 0, ',', '.') }}
                &middot; sejak {{ $shift?->opened_at?->format('H:i') }}
            </p>
        </div>
        <a href="{{ route('tenant.shift.close') }}"
            class="shrink-0 rounded-xl bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700">Tutup Shift</a>
    </div>

    {{-- Tombol scan raksasa --}}
    <button type="button" @click="startScan()" x-show="!scanning"
        class="w-full rounded-2xl bg-indigo-600 px-5 py-6 text-center shadow-xl shadow-indigo-600/25 transition active:scale-[0.99] hover:bg-indigo-700">
        <span class="block text-3xl">📷</span>
        <span class="mt-1 block text-base font-extrabold text-white">Scan Barcode IMEI</span>
        <span class="mt-0.5 block text-xs text-indigo-200">Arahkan kamera ke barcode pada kotak / label HP</span>
    </button>

    {{-- Area kamera --}}
    <div x-cloak x-show="scanning" class="mb-4 rounded-2xl border border-slate-200 bg-slate-900 p-3 shadow-lg">
        <div id="scanner-region" class="overflow-hidden rounded-xl bg-black" style="min-height: 240px;"></div>
        <div class="mt-3 flex gap-2">
            <button type="button" @click="stopScan()"
                class="flex-1 rounded-xl bg-white/10 px-4 py-3 text-sm font-bold text-white hover:bg-white/20">Tutup Kamera</button>
        </div>
        <p class="mt-2 text-center text-[11px] text-slate-400" x-text="scanStatus"></p>
    </div>

    {{-- Input manual --}}
    <form id="pos-lookup-form" @submit.prevent="lookupManual()" class="mb-4 flex gap-2">
        <input id="pos-imei-input" x-model="manualImei" type="text" inputmode="numeric" autocomplete="off"
            class="block w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm tracking-wide focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
            placeholder="Ketik / tempel nomor IMEI lalu Enter">
        <button type="submit"
            class="shrink-0 rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800">Cek</button>
    </form>

    {{-- Hasil deteksi --}}
    <div x-cloak x-show="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" x-text="error"></div>

    <div x-cloak x-show="result" class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Detail HP ditemukan</p>
                    <p class="mt-0.5 break-all font-mono text-sm font-bold text-slate-900" x-text="result.imei"></p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="result.tipe_hp || '—'"></p>
                    <p class="text-xs text-slate-500" x-text="result.brand || ''"></p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700"
                            x-text="'Rp ' + Number(result.price_sell || 0).toLocaleString('id-ID')"></span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold"
                            :class="result.status === 'available' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                            x-text="result.status"></span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                            x-text="result.age_days + ' hari di stok'"></span>
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                Checkout &amp; pembayaran aktif di Slice 6. Saat ini baru deteksi barang.
            </div>
    </div>

    <p class="mt-4 text-center text-[11px] text-slate-400">{{ $stockCount }} unit stok tersedia di toko ini.</p>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function posScanner() {
    return {
        scanning: false,
        scanStatus: '',
        manualImei: '',
        result: null,
        error: null,
        scanner: null,

        init() {
            this.$watch('scanning', (v) => { if (!v) this.stopScan(); });
        },

        async startScan() {
            this.error = null;
            this.result = null;
            this.scanning = true;
            this.scanStatus = 'Meminta izin kamera...';

            await this.$nextTick();

            try {
                this.scanner = new Html5Qrcode('scanner-region');
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 260, height: 160 } },
                    (decoded) => this.onDetected(decoded),
                    () => {}
                );
                this.scanStatus = 'Kamera aktif — arahkan ke barcode IMEI.';
            } catch (e) {
                this.scanning = false;
                this.error = 'Kamera tidak bisa dibuka: ' + (e?.message || e);
            }
        },

        async stopScan() {
            this.scanning = false;
            if (this.scanner) {
                try { await this.scanner.stop(); await this.scanner.clear(); } catch (e) {}
                this.scanner = null;
            }
            this.scanStatus = '';
        },

        onDetected(text) {
            const imei = String(text).replace(/\D/g, '');
            if (!imei) return;

            if (navigator.vibrate) navigator.vibrate(80);
            this.stopScan();
            this.lookup(imei);
        },

        lookupManual() {
            const imei = this.manualImei.replace(/\D/g, '');
            if (!imei) { this.error = 'Isi nomor IMEI dulu.'; return; }
            this.lookup(imei);
        },

        async lookup(imei) {
            this.error = null;
            this.result = null;

            try {
                const res = await fetch('{{ route('tenant.pos.lookup') }}?imei=' + encodeURIComponent(imei), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                if (!res.ok) {
                    this.error = 'IMEI ' + imei + ' tidak ditemukan di stok toko ini.';
                    return;
                }

                this.result = await res.json();
                this.manualImei = '';
            } catch (e) {
                this.error = 'Gagal memeriksa IMEI: ' + (e?.message || e);
            }
        },
    };
}
</script>
@endsection
