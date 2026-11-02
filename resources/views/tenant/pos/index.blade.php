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

  {{-- Tombol scan --}}
  <button type="button" @click="startScan()" x-show="!scanning"
    class="w-full rounded-2xl bg-indigo-600 px-5 py-6 text-center shadow-xl shadow-indigo-600/25 transition active:scale-[0.99] hover:bg-indigo-700">
    <span class="block text-3xl">📷</span>
    <span class="mt-1 block text-base font-extrabold text-white">Scan Barcode IMEI</span>
    <span class="mt-0.5 block text-xs text-indigo-200">Scan HP untuk masuk keranjang nota</span>
  </button>

  {{-- Area kamera --}}
  <div x-cloak x-show="scanning" class="mb-4 rounded-2xl border border-slate-200 bg-slate-900 p-3 shadow-lg">
    <div id="scanner-region" class="overflow-hidden rounded-xl bg-black" style="min-height:220px;max-height:55vh;"></div>
    <div class="mt-3 flex gap-2">
      <button type="button" @click="stopScan()" class="flex-1 rounded-xl bg-white/10 px-4 py-3 text-sm font-bold text-white hover:bg-white/20">Tutup Kamera</button>
    </div>
    <p class="mt-2 text-center text-[11px] text-slate-400" x-text="scanStatus"></p>
  </div>

  {{-- Input manual --}}
  <form id="pos-lookup-form" @submit.prevent="lookupManual()" class="mb-4 flex gap-2">
    <input id="pos-imei-input" x-model="manualImei" type="text" inputmode="numeric" autocomplete="off"
      class="block w-full rounded-xl border border-slate-300 px-4 py-3 font-mono text-sm tracking-wide focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
      placeholder="Ketik / tempel nomor IMEI lalu Enter">
    <button type="submit" class="shrink-0 rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800">Tambah</button>
  </form>

  {{-- Notifikasi --}}
  <div x-cloak x-show="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" x-text="error"></div>
  <div x-cloak x-show="success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700" x-text="success"></div>

  {{-- Panduan singkat 3 langkah: tampil selama keranjang masih kosong --}}
  <div x-cloak x-show="!cart.length" class="mb-4 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3">
    <p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Cara jualan — 3 langkah</p>
    <ol class="mt-1.5 space-y-1 text-sm text-indigo-900">
      <li><strong>1.</strong> Scan barcode atau ketik IMEI HP.</li>
      <li><strong>2.</strong> Set diskon &amp; metode bayar (Cash/QRIS/Transfer/Campur).</li>
      <li><strong>3.</strong> Tekan tombol hijau "Bayar". Struk tersimpan otomatis.</li>
    </ol>
    <a href="{{ route('tenant.guide') }}" class="mt-2 inline-block text-xs font-semibold text-indigo-600 hover:underline">Panduan lengkap &rarr;</a>
  </div>

  {{-- KERANJANG NOTA --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
      <p class="text-sm font-bold text-slate-900">Nota &middot; <span x-text="cart.length + ' item'">0 item</span></p>
      <button type="button" @click="clearCart()" x-show="cart.length" class="text-xs font-semibold text-red-600 hover:underline">Kosongkan</button>
    </div>

    <div class="max-h-64 divide-y divide-slate-100 overflow-y-auto">
      <template x-for="(c, i) in cart" :key="c.inventory_id">
        <div class="flex items-center gap-3 px-4 py-2.5">
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-slate-800" x-text="c.tipe_hp + (c.brand ? ' · ' + c.brand : '')"></p>
            <p class="truncate font-mono text-[11px] text-slate-500" x-text="c.imei"></p>
          </div>
          <p class="shrink-0 text-sm font-bold text-slate-900" x-text="rupiah(c.price_sell)"></p>
          <button type="button" @click="removeItem(i)" class="shrink-0 grid h-7 w-7 place-items-center rounded-lg bg-red-50 text-red-600">✕</button>
        </div>
      </template>
      <div x-show="!cart.length" class="px-4 py-6 text-center text-sm text-slate-400">Belum ada item. Scan atau ketik IMEI.</div>
    </div>
  </div>

  {{-- Ringkasan + diskon + metode bayar --}}
  <div x-show="cart.length" x-cloak class="mt-4 space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex justify-between text-sm">
      <span class="text-slate-600">Subtotal</span>
      <span class="font-bold text-slate-900" x-text="rupiah(gross())"></span>
    </div>

    {{-- Diskon --}}
    <div class="flex gap-2">
      <select x-model="discountType" class="rounded-xl border border-slate-300 px-2 py-2 text-sm">
        <option value="nominal">Rp</option>
        <option value="percent">%</option>
      </select>
      <input x-data="money(0)" x-model="discountDisplay" @input="discountDisplay = fmt(discountDisplay); discountValue = raw" type="text" inputmode="numeric"
        :placeholder="discountType==='percent' ? 'Diskon %' : 'Potongan Rp'"
        class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    </div>
    <div class="flex justify-between text-sm" x-show="discountAmount() > 0">
      <span class="text-slate-600">Diskon</span>
      <span class="font-bold text-emerald-600" x-text="'- ' + rupiah(discountAmount())"></span>
    </div>
    <div class="flex justify-between border-t border-slate-200 pt-2 text-base">
      <span class="font-bold text-slate-900">Total</span>
      <span class="font-extrabold text-indigo-700" x-text="rupiah(total())"></span>
    </div>

    {{-- Metode bayar --}}
    <div class="rounded-xl border border-slate-200 p-2.5">
      <div class="grid grid-cols-4 gap-1 text-center">
        <button type="button" @click="setMethod('cash')" :class="method==='cash' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'"
          class="rounded-lg px-2 py-2 text-xs font-bold">Cash</button>
        <button type="button" @click="setMethod('qris')" :class="method==='qris' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'"
          class="rounded-lg px-2 py-2 text-xs font-bold">QRIS</button>
        <button type="button" @click="setMethod('transfer')" :class="method==='transfer' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'"
          class="rounded-lg px-2 py-2 text-xs font-bold">Transfer</button>
        <button type="button" @click="setMethod('mixed')" :class="method==='mixed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'"
          class="rounded-lg px-2 py-2 text-xs font-bold">Campur</button>
      </div>
      <div x-show="method==='mixed'" class="mt-2 grid grid-cols-3 gap-2">
        <div><label class="text-[11px] text-slate-500">Cash</label><input x-data="money(0)" x-model="payCashDisplay" @input="payCashDisplay = fmt(payCashDisplay); pay.cash = raw" type="text" inputmode="numeric" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"></div>
        <div><label class="text-[11px] text-slate-500">QRIS</label><input x-data="money(0)" x-model="payQrisDisplay" @input="payQrisDisplay = fmt(payQrisDisplay); pay.qris = raw" type="text" inputmode="numeric" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"></div>
        <div><label class="text-[11px] text-slate-500">Transfer</label><input x-data="money(0)" x-model="payTransferDisplay" @input="payTransferDisplay = fmt(payTransferDisplay); pay.transfer = raw" type="text" inputmode="numeric" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"></div>
      </div>
      <p x-show="method==='mixed' && (pay.cash+pay.qris+pay.transfer) !== total()" class="mt-1.5 text-[11px] font-semibold text-red-600"
        x-text="'Jumlah harus Rp ' + rupiah(total())"></p>
    </div>

    <button type="button" @click="confirmCheckout()" :disabled="processing || !cart.length"
      class="w-full rounded-xl bg-emerald-600 px-5 py-4 text-base font-extrabold text-white shadow-lg shadow-emerald-600/25 transition active:scale-[0.99] hover:bg-emerald-700 disabled:opacity-60">
      Bayar &middot; <span x-text="rupiah(total())"></span>
    </button>
  </div>

  <p class="mt-4 text-center text-[11px] text-slate-400">{{ $stockCount }} unit stok tersedia di toko ini.</p>

  {{-- ===== MODAL KONFIRMASI UPSELL (full-screen kuning) ===== --}}
  <div x-cloak x-show="upsell" x-effect="document.body.style.overflow = upsell ? 'hidden' : ''"
    class="fixed inset-0 z-50 flex flex-col overscroll-contain bg-amber-400">
    <div class="flex items-center justify-between border-b-2 border-amber-600/30 px-4 py-3">
      <p class="text-sm font-extrabold uppercase tracking-wide text-amber-950">Konfirmasi Pembayaran</p>
      <span class="rounded-full bg-amber-950/10 px-2 py-0.5 font-mono text-xs font-bold text-amber-950" x-text="rupiah(total())"></span>
    </div>

    <div class="flex min-h-0 flex-1 flex-col items-center justify-center overflow-y-auto px-5 py-6 text-center">
      <div class="text-5xl leading-none">⚠️</div>
      <h2 class="mt-4 text-2xl font-extrabold uppercase leading-tight tracking-tight text-amber-950 sm:text-3xl">
        Tawarkan bundling Anti-Gores &amp; Casing sebelum bayar!
      </h2>
      <p class="mt-3 max-w-sm text-sm font-semibold text-amber-900">Verifikasi semua item di nota sudah benar.</p>

      <div class="mt-4 w-full max-w-sm rounded-2xl bg-white/70 px-4 py-3 text-left">
        <template x-for="(c,i) in cart" :key="c.inventory_id">
          <div class="flex justify-between py-1 text-sm">
            <span class="text-amber-950" x-text="c.tipe_hp"></span>
            <span class="font-bold text-amber-950" x-text="rupiah(c.price_sell)"></span>
          </div>
        </template>
        <div class="mt-1 flex justify-between border-t border-amber-800/20 pt-1 text-base font-extrabold text-amber-950">
          <span>Total</span><span x-text="rupiah(total())"></span>
        </div>
        <p class="mt-1 text-[11px] font-semibold text-amber-800" x-text="'Metode: ' + methodLabel()"></p>
      </div>

      <div x-cloak x-show="checkoutError" class="mt-4 w-full max-w-sm rounded-xl border-2 border-red-700 bg-red-50 px-4 py-3 text-left">
        <p class="text-sm font-bold text-red-700" x-text="checkoutError"></p>
        <a x-show="waLink" :href="waLink" target="_blank" rel="noopener" class="mt-2 inline-block rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white">Hubungi Admin via WhatsApp</a>
      </div>
    </div>

    <div class="shrink-0 space-y-2 border-t-2 border-amber-600/30 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
      <button type="button" @click="closeUpsell()" :disabled="processing" class="w-full rounded-xl border-2 border-amber-950/30 bg-white/60 px-5 py-4 text-base font-bold text-amber-950 hover:bg-white disabled:opacity-60">Kembali (Tambah Item)</button>
      <button type="button" @click="processCheckout()" :disabled="processing" class="w-full rounded-xl bg-amber-950 px-5 py-4 text-base font-extrabold text-white shadow-lg transition active:scale-[0.99] hover:bg-black disabled:opacity-60">
        <span x-show="!processing">Lanjutkan Pembayaran</span>
        <span x-cloak x-show="processing">Memproses...</span>
      </button>
    </div>
  </div>
</div>

<style>
  #scanner-region video, #imei-scanner-region video { width: 100%; height: 100%; object-fit: cover; display: block; }
</style>
<script src="/js/imei-scanner.js"></script>
<script>
@include('tenant.pos.pos_js')
</script>
@endsection
