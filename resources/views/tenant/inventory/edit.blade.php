@php $errors = $errors ?? new Illuminate\Support\ViewErrorBag; @endphp
@extends('tenant.layout')

@section('title', 'Edit Stok')
@section('heading', 'Edit Stok')

@section('content')

<div class="mx-auto max-w-2xl">
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{ open: true }">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <span class="text-sm font-bold text-slate-900">Edit IMEI {{ $inventory->imei }}</span>
            <a href="{{ route('tenant.inventory.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">← Kembali</a>
        </div>

        <div class="p-5">
            @if ($inventory->status === 'sold')
                <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700">
                    Unit ini sudah terjual — edit harga/tipe hanya berpengaruh ke histori, bukan transaksi.
                </div>
            @endif

            <form method="POST" action="{{ route('tenant.inventory.update', $inventory) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="tipe_hp" class="block text-sm font-medium text-slate-700">Tipe HP</label>
                        <input id="tipe_hp" name="tipe_hp" type="text" required value="{{ old('tipe_hp', $inventory->product?->tipe_hp) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="Samsung Galaxy A15 8/256">
                        @error('tipe_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="brand" class="block text-sm font-medium text-slate-700">Brand</label>
                        <input id="brand" name="brand" type="text" value="{{ old('brand', $inventory->product?->brand) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="Samsung">
                    </div>

                    <div>
                        <label for="price_buy" class="block text-sm font-medium text-slate-700">
                            Harga Modal / HPP (Rp)
                            <span class="font-normal text-slate-400">&mdash; boleh dikosongkan</span>
                        </label>
                        <input id="price_buy" name="price_buy" type="text" inputmode="numeric"
                            x-data="money({{ (int) old('price_buy', $inventory->product?->price_buy ?? 0) }})"
                            x-model="display" @input="display = fmt(display)"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="0">
                        @error('price_buy') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="price_sell" class="block text-sm font-medium text-slate-700">Harga Jual (Rp)</label>
                        <input id="price_sell" name="price_sell" type="text" inputmode="numeric" required
                            x-data="money({{ (int) old('price_sell', $inventory->product?->price_sell ?? 0) }})"
                            x-model="display" @input="display = fmt(display)"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="2.500.000">
                        @error('price_sell') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="entry_date" class="block text-sm font-medium text-slate-700">Tanggal Masuk</label>
                    <input id="entry_date" name="entry_date" type="date" required value="{{ old('entry_date', $inventory->entry_date->format('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('entry_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <label for="imei" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">IMEI</label>
                    <input id="imei" name="imei" type="text" required inputmode="numeric"
                        value="{{ old('imei', $inventory->imei) }}"
                        @if($inventory->status === 'sold') disabled @endif
                        class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 font-mono text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 disabled:bg-slate-100 disabled:text-slate-500">
                    @if($inventory->status === 'sold')
                        <p class="mt-1 text-xs text-slate-500">IMEI unit terjual dikunci agar riwayat transaksi tetap utuh.</p>
                    @else
                        <p class="mt-1 text-xs text-slate-500">Salah ketik? Perbaiki di sini, maksimal 15 digit angka.</p>
                    @endif
                    @error('imei') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 sm:w-auto">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('tenant.inventory.index') }}"
                        class="w-full rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 sm:w-auto">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
