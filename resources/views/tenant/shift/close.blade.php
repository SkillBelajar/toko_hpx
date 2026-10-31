@extends('tenant.layout')

@section('title', 'Tutup Shift')
@section('heading', 'Tutup Shift')

@section('content')
<div class="mx-auto max-w-md">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-red-50 text-red-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M10.3 4.6L2.9 17a1.8 1.8 0 001.6 2.7h15a1.8 1.8 0 001.6-2.7L13.7 4.6a1.9 1.9 0 00-3.4 0z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Hitung uang fisik di laci</h2>
            <p class="mt-1 text-sm text-slate-600">Masukkan total uang tunai yang tersisa di laci sebelum shift ditutup.</p>
        </div>

        <dl class="mt-5 space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Kasir</dt>
                <dd class="font-semibold text-slate-800">{{ $shift->user?->name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Shift dibuka</dt>
                <dd class="font-semibold text-slate-800">{{ $shift->opened_at->format('d M Y H:i') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Modal laci awal</dt>
                <dd class="font-semibold text-slate-800">Rp {{ number_format((float) $shift->opening_amount, 0, ',', '.') }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Penjualan shift ini</dt>
                <dd class="font-semibold text-slate-800">
                    Rp {{ number_format((float) $totalSales, 0, ',', '.') }}
                    <span class="text-xs font-normal text-slate-500">({{ $trxCount }} transaksi)</span>
                </dd>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-2">
                <dt class="font-semibold text-slate-700">Uang seharusnya (sistem)</dt>
                <dd class="font-bold text-slate-900">Rp {{ number_format((float) $expectedAmount, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <form method="POST" action="{{ route('tenant.shift.update') }}" class="mt-5 space-y-4" x-data="{ closing: {{ (int) old('closing_amount', 0) }}, expected: {{ (int) $expectedAmount }} }"
            onsubmit="return confirm('Tutup shift sekarang? Shift yang sudah ditutup tidak bisa dibuka kembali.')">
            @csrf
            @method('PUT')

            <div>
                <label for="closing_amount" class="block text-sm font-medium text-slate-700">Uang Fisik Laci Akhir (Rp)</label>
                <div class="relative mt-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">Rp</span>
                    <input id="closing_amount" name="closing_amount" type="number" inputmode="numeric" min="0" step="1000"
                        required autofocus x-model.number="closing" value="{{ old('closing_amount') }}"
                        class="block w-full rounded-xl border border-slate-300 py-4 pl-12 pr-4 text-2xl font-bold tracking-tight text-slate-900 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                        placeholder="0">
                </div>
                @error('closing_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Preview selisih langsung --}}
            <div class="rounded-xl border px-4 py-3 text-sm"
                :class="(closing - expected) < 0 ? 'border-red-200 bg-red-50 text-red-700' : ((closing - expected) > 0 ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-emerald-200 bg-emerald-50 text-emerald-700')">
                <p class="font-semibold">
                    Selisih:
                    <span x-text="'Rp ' + (closing - expected).toLocaleString('id-ID')"></span>
                </p>
                <p class="mt-0.5 text-xs" x-show="(closing - expected) < 0">Uang fisik kurang dari catatan sistem.</p>
                <p class="mt-0.5 text-xs" x-show="(closing - expected) > 0">Uang fisik lebih dari catatan sistem.</p>
                <p class="mt-0.5 text-xs" x-show="(closing - expected) === 0">Pas, tidak ada selisih.</p>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-red-600 px-5 py-4 text-base font-bold text-white shadow-lg shadow-red-600/20 hover:bg-red-700">
                Tutup Shift
            </button>

            <a href="{{ route('tenant.pos.index') }}"
                class="block w-full rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Batal, lanjut kasir
            </a>
        </form>
    </div>

    <p class="mt-4 text-center text-xs text-slate-500">
        Uang seharusnya = modal awal + seluruh penjualan shift ini. Selisih di luar 0 dicatat sebagai temuan kas.
    </p>
</div>
@endsection
