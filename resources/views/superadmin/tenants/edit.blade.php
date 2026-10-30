@extends('superadmin.layout')

@section('title', 'Edit Toko')
@section('heading', 'Edit Toko')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Toko</p>
        <p class="mt-1 text-lg font-bold text-slate-900">{{ $tenant->nama_toko }}</p>
        <p class="text-sm text-slate-600">
            Pemilik: {{ $tenant->users->first()?->name ?? '—' }}
            @if ($tenant->no_telp) &middot; {{ $tenant->no_telp }} @endif
        </p>
    </div>

    <form method="POST" action="{{ route('superadmin.tenants.update', $tenant) }}"
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="package_id" class="block text-sm font-medium text-slate-700">Paket Aktif</label>
                <select id="package_id" name="package_id"
                    class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">— Tanpa Paket —</option>
                    @foreach ($packages as $package)
                        <option value="{{ $package->id }}" @selected((string) old('package_id', $tenant->package_id) === (string) $package->id)>
                            {{ $package->name }} — Rp {{ number_format($package->price, 0, ',', '.') }}/bulan
                        </option>
                    @endforeach
                </select>
                @error('package_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="expired_date" class="block text-sm font-medium text-slate-700">Expired Date</label>
                <input id="expired_date" name="expired_date" type="date"
                    value="{{ old('expired_date', $tenant->expired_date?->format('Y-m-d')) }}"
                    class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                <p class="mt-1 text-xs text-slate-500">Kosongkan jika belum ada masa aktif.</p>
                @error('expired_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <a href="{{ route('superadmin.tenants.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
