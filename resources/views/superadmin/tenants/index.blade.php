@extends('superadmin.layout')

@section('title', 'Kelola Toko')
@section('heading', 'Kelola Toko')

@section('content')
<p class="mb-4 text-sm text-slate-600">Total {{ $tenants->count() }} toko terdaftar.</p>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[52rem] text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Nama Toko</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Pemilik</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Kontak</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Paket Aktif</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold">Expired Date</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($tenants as $tenant)
                    @php
                        $owner = $tenant->users->first();
                        $isExpired = $tenant->expired_date && $tenant->expired_date->isPast();
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">{{ $tenant->nama_toko }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                            {{ $owner?->name ?? '—' }}
                            <span class="block text-xs text-slate-400">{{ $owner?->email }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $tenant->no_telp ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($tenant->package)
                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ $tenant->package->name }}</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Tanpa Paket</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($tenant->expired_date)
                                <span class="{{ $isExpired ? 'font-semibold text-red-600' : 'text-slate-700' }}">
                                    {{ $tenant->expired_date->format('d M Y') }}
                                </span>
                                @if ($isExpired)
                                    <span class="ml-1 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-bold text-red-600">Expired</span>
                                @endif
                            @else
                                <span class="text-xs text-slate-400">Tidak ada</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('superadmin.tenants.edit', $tenant) }}"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada toko terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
