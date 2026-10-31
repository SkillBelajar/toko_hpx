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

    {{-- Status kuota transaksi --}}
    <div class="mb-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600">
        Transaksi terpakai:
        <strong class="text-slate-900" x-text="trxUsed">{{ $transactionUsed }}</strong>
        / {{ $transactionLimit ?? '∞' }}
        @if (! $packageActive)
            <span class="ml-2 inline-block rounded-full bg-red-100 px-2 py-0.5 font-bold text-red-700">Paket expired</span>
        @endif
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

    {{-- Notifikasi --}}
    <div x-cloak x-show="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" x-text="error"></div>
    <div x-cloak x-show="success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" x-text="success"></div>

    {{-- Card hasil scan --}}
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
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700"
                        x-text="result.status"></span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                        x-text="result.age_days + ' hari di stok'"></span>
                </div>
            </div>
        </div>

        <button type="button" @click="openUpsell()" :disabled="processing"
            class="mt-5 w-full rounded-xl bg-emerald-600 px-5 py-4 text-base font-extrabold text-white shadow-lg shadow-emerald-600/25 transition active:scale-[0.99] hover:bg-emerald-700 disabled:opacity-60">
            Checkout / Proses Pembayaran
        </button>
    </div>

    <p class="mt-4 text-center text-[11px] text-slate-400">{{ $stockCount }} unit stok tersedia di toko ini.</p>

    {{-- ===== MODAL UPSELL (layar penuh, kuning/oranye) ===== --}}
    <div x-cloak x-show="upsell" class="fixed inset-0 z-50 flex flex-col bg-amber-400">

        <div class="flex items-center justify-between border-b-2 border-amber-600/30 px-4 py-3">
            <p class="text-sm font-extrabold uppercase tracking-wide text-amber-950">Pengingat Upsell</p>
            <span class="rounded-full bg-amber-950/10 px-2 py-0.5 font-mono text-xs font-bold text-amber-950"
                x-text="'Rp ' + Number(result?.price_sell || 0).toLocaleString('id-ID')"></span>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center px-5 py-8 text-center">
            <div class="text-6xl leading-none">⚠️</div>

            <h2 class="mt-5 text-2xl font-extrabold uppercase leading-tight tracking-tight text-amber-950 sm:text-3xl">
                PERINGATAN KASIR:<br>Tawarkan bundling Anti-Gores &amp; Casing sekarang!
            </h2>

            <p class="mt-4 max-w-sm text-sm font-semibold text-amber-900">
                Peluang margin tertinggi ada di detik ini. Tanyakan sebelum uang diterima.
            </p>

            <div class="mt-5 w-full max-w-sm rounded-2xl bg-white/70 px-4 py-3 text-left">
                <p class="text-[11px] font-bold uppercase tracking-wide text-amber-800">Barang</p>
                <p class="text-sm font-bold text-amber-950" x-text="(result?.tipe_hp || '—') + (result?.brand ? ' · ' + result.brand : '')"></p>
                <p class="mt-0.5 font-mono text-[11px] text-amber-800" x-text="result?.imei"></p>
            </div>

            <div x-cloak x-show="checkoutError" class="mt-4 w-full max-w-sm rounded-xl border-2 border-red-700 bg-red-50 px-4 py-3 text-left">
                <p class="text-sm font-bold text-red-700" x-text="checkoutError"></p>
                <a x-show="waLink" :href="waLink" target="_blank" rel="noopener"
                    class="mt-2 inline-block rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white">
                    Hubungi Admin via WhatsApp
                </a>
            </div>
        </div>

        <div class="space-y-2 border-t-2 border-amber-600/30 p-4">
            <button type="button" @click="closeUpsell()" :disabled="processing"
                class="w-full rounded-xl border-2 border-amber-950/30 bg-white/60 px-5 py-4 text-base font-bold text-amber-950 hover:bg-white disabled:opacity-60">
                Kembali (Tambah Item)
            </button>
            <button type="button" @click="processCheckout()" :disabled="processing"
                class="w-full rounded-xl bg-amber-950 px-5 py-4 text-base font-extrabold text-white shadow-lg transition active:scale-[0.99] hover:bg-black disabled:opacity-60">
                <span x-show="!processing">Lanjutkan Pembayaran</span>
                <span x-cloak x-show="processing">Memproses...</span>
            </button>
        </div>
    </div>

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
        success: null,
        scanner: null,

        trxUsed: {{ $transactionUsed }},
        trxLimit: {{ $transactionLimit ?? 'null' }},

        upsell: false,
        processing: false,
        checkoutError: null,
        waLink: null,

        init() {
            this.$watch('scanning', (v) => { if (!v) this.stopScan(); });
        },

        async startScan() {
            this.error = null;
            this.success = null;
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
            this.success = null;
            this.result = null;

            try {
                const res = await fetch('{{ route('tenant.pos.lookup') }}?imei=' + encodeURIComponent(imei), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok) {
                    this.error = body.message || ('IMEI ' + imei + ' tidak ditemukan di stok toko ini.');
                    return;
                }

                this.result = body;
                this.manualImei = '';
            } catch (e) {
                this.error = 'Gagal memeriksa IMEI: ' + (e?.message || e);
            }
        },

        openUpsell() {
            if (!this.result) return;
            this.checkoutError = null;
            this.waLink = null;
            this.upsell = true;
        },

        closeUpsell() {
            this.upsell = false;
            this.checkoutError = null;
        },

        async processCheckout() {
            if (!this.result?.inventory_id) {
                this.checkoutError = 'Scan ulang IMEI dulu.';
                return;
            }

            this.processing = true;
            this.checkoutError = null;

            try {
                const res = await fetch('{{ route('tenant.pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ inventory_id: this.result.inventory_id }),
                });

                const body = await res.json().catch(() => ({}));

                if (!res.ok || !body.ok) {
                    this.checkoutError = body.message || 'Checkout gagal.';
                    this.waLink = body.wa_link || null;
                    return;
                }

                this.upsell = false;
                this.result = null;
                if (typeof body.transaction_used === 'number') this.trxUsed = body.transaction_used;
                this.success = body.message + ' Total Rp ' + Number(body.total_price || 0).toLocaleString('id-ID')
                    + (body.transaction_limit ? ' · transaksi ' + body.transaction_used + '/' + body.transaction_limit : '');

                if (navigator.vibrate) navigator.vibrate([40, 60, 40]);
            } catch (e) {
                this.checkoutError = 'Checkout gagal: ' + (e?.message || e);
            } finally {
                this.processing = false;
            }
        },
    };
}
</script>
@endsection
