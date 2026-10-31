@extends('tenant.layout')

@section('title', 'Buka Shift')
@section('heading', 'Buka Shift')

@section('content')
<div class="mx-auto max-w-md">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h4M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Hitung uang di laci sekarang</h2>
            <p class="mt-1 text-sm text-slate-600">
                Masukkan total uang fisik yang ada di laci. Angka ini jadi patokan pemeriksaan saat tutup shift.
            </p>
        </div>

        @if ($lastShift)
            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                Shift terakhir ditutup {{ $lastShift->closed_at?->format('d M Y H:i') ?? '—' }}
                &middot; Uang fisik akhir Rp {{ number_format((float) ($lastShift->closing_amount ?? 0), 0, ',', '.') }}
                &middot; Selisih Rp {{ number_format((float) ($lastShift->variance ?? 0), 0, ',', '.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.shift.store') }}" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="opening_amount" class="block text-sm font-medium text-slate-700">Modal Laci Awal (Rp)</label>
                <div class="relative mt-1">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">Rp</span>
                    <input id="opening_amount" name="opening_amount" type="number" inputmode="numeric" min="0" step="1000"
                        required autofocus value="{{ old('opening_amount') }}"
                        class="block w-full rounded-xl border border-slate-300 py-4 pl-12 pr-4 text-2xl font-bold tracking-tight text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                        placeholder="0">
                </div>
                @error('opening_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ([100000, 200000, 500000, 1000000] as $preset)
                    <button type="button"
                        onclick="document.getElementById('opening_amount').value={{ $preset }}"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        {{ number_format($preset, 0, ',', '.') }}
                    </button>
                @endforeach
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-emerald-600 px-5 py-4 text-base font-bold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700">
                Buka Shift Sekarang
            </button>
        </form>
    </div>

    <p class="mt-4 text-center text-xs text-slate-500">
        Modal laci dicatat per kasir, bukan per toko &mdash; supaya selisih kas bisa ditelusuri.
    </p>
</div>
@endsection
