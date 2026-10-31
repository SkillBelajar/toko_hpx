@extends('tenant.layout')

@section('title', 'Laporan Shift')
@section('heading', 'Laporan Shift')

@section('content')
<div class="mx-auto max-w-5xl">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-sm text-slate-600">Histori buka/tutup laci per kasir. Selisih minus berarti uang kurang.</p>
            @if ($shiftsWithVariance > 0)
                <p class="mt-1 text-xs font-semibold text-red-600">{{ $shiftsWithVariance }} shift punya selisih minus.</p>
            @endif
        </div>
        <a href="{{ route('tenant.reports') }}"
            class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Laporan Penjualan
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[56rem] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Kasir</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Buka</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Tutup</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Uang Awal</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Penjualan</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Seharusnya</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Uang Fisik</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($shifts as $shift)
                        @php
                            $variance = $shift->variance !== null ? (float) $shift->variance : null;
                            $isLoss = $variance !== null && $variance < 0;
                        @endphp
                        <tr class="{{ $isLoss ? 'bg-red-50' : 'hover:bg-slate-50/70' }}">
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="font-semibold text-slate-900">{{ $shift->user?->name ?? '—' }}</span>
                                @if ($shift->isActive())
                                    <span class="ml-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">Aktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $shift->opened_at?->format('d M Y H:i') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                {{ $shift->closed_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                Rp {{ number_format((float) $shift->opening_amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                Rp {{ number_format((float) ($shift->sales_total ?? 0), 0, ',', '.') }}
                                <span class="block text-xs text-slate-400">{{ $shift->transactions_count }} trx</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">
                                {{ $shift->expected_amount !== null ? 'Rp '.number_format((float) $shift->expected_amount, 0, ',', '.') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                {{ $shift->closing_amount !== null ? 'Rp '.number_format((float) $shift->closing_amount, 0, ',', '.') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($variance === null)
                                    <span class="text-xs text-slate-400">Belum ditutup</span>
                                @elseif ($variance < 0)
                                    <span class="font-bold text-red-600">Rp {{ number_format($variance, 0, ',', '.') }}</span>
                                @elseif ($variance > 0)
                                    <span class="font-semibold text-amber-600">+Rp {{ number_format($variance, 0, ',', '.') }}</span>
                                @else
                                    <span class="font-semibold text-emerald-600">Pas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada histori shift.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $shifts->links() }}</div>
</div>
@endsection
