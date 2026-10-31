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
                        <label for="price_sell" class="block text-sm font-medium text-slate-700">Harga Jual (Rp)</label>
                        <input id="price_sell" name="price_sell" type="number" min="0" step="1000" required value="{{ old('price_sell', $inventory->product?->price_sell) }}"
                            class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            placeholder="2500000">
                        @error('price_sell') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="entry_date" class="block text-sm font-medium text-slate-700">Tanggal Masuk</label>
                    <input id="entry_date" name="entry_date" type="date" required value="{{ old('entry_date', $inventory->entry_date->format('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    @error('entry_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">IMEI (tidak bisa diubah)</span>
                    <p class="mt-1 font-mono text-sm font-semibold text-slate-900">{{ $inventory->imei }}</p>
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
