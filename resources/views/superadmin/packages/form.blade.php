@extends('superadmin.layout')

@php
    $isEdit = $package->exists;
@endphp

@section('title', $isEdit ? 'Edit Paket' : 'Paket Baru')
@section('heading', $isEdit ? 'Edit Paket' : 'Paket Baru')

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST"
        action="{{ $isEdit ? route('superadmin.packages.update', $package) : route('superadmin.packages.store') }}"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nama Paket</label>
                <input id="name" name="name" type="text" required value="{{ old('name', $package->name) }}"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    placeholder="Mis. Pro">
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-slate-700">Harga Bulanan (Rp)</label>
                <input id="price" name="price" type="number" min="0" step="1000" required value="{{ old('price', $package->price) }}"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                    placeholder="90000">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="total_transaction_limit" class="block text-sm font-medium text-slate-700">Limit Total Transaksi</label>
                    <input id="total_transaction_limit" name="total_transaction_limit" type="number" min="1"
                        value="{{ old('total_transaction_limit', $package->total_transaction_limit) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="Kosongkan = unlimited">
                </div>

                <div>
                    <label for="total_imei_limit" class="block text-sm font-medium text-slate-700">Limit Total IMEI</label>
                    <input id="total_imei_limit" name="total_imei_limit" type="number" min="1"
                        value="{{ old('total_imei_limit', $package->total_imei_limit) }}"
                        class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="Kosongkan = unlimited">
                </div>
            </div>

            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $package->is_default))
                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-600">
                    Jadikan paket default untuk toko baru.
                    <span class="block text-xs text-slate-500">Hanya satu paket yang bisa default; flag pada paket lain otomatis dimatikan.</span>
                </span>
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('superadmin.packages.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Paket' }}
            </button>
        </div>
    </form>
</div>
@endsection
