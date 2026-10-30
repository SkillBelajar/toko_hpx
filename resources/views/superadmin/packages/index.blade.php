@extends('superadmin.layout')

@section('title', 'Kelola Paket')
@section('heading', 'Kelola Paket')

@section('content')
<div class="mb-4 flex items-center justify-between gap-3">
    <p class="text-sm text-slate-600">Total {{ $packages->count() }} paket. Limit kosong = unlimited.</p>
    <a href="{{ route('superadmin.packages.create') }}"
        class="shrink-0 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
        + Paket Baru
    </a>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[46rem] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Nama Paket</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Harga / Bulan</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Limit Transaksi</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Limit IMEI</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Toko</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Default</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($packages as $package)
                    <tr class="hover:bg-slate-50/70">
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $package->name }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                            {{ $package->total_transaction_limit ?? 'Unlimited' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                            {{ $package->total_imei_limit ?? 'Unlimited' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">{{ $package->tenants_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($package->is_default)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">Default</span>
                            @else
                                <span class="text-xs text-slate-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('superadmin.packages.edit', $package) }}"
                                    class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                <form method="POST" action="{{ route('superadmin.packages.destroy', $package) }}"
                                    onsubmit="return confirm('Hapus paket {{ $package->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada paket.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
