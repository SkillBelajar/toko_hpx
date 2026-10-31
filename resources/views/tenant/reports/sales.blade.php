@extends('tenant.layout')

@section('title', 'Laporan Penjualan')
@section('heading', 'Laporan Penjualan')

@section('content')
<div class="mx-auto max-w-4xl">

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hari Ini</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                Rp {{ number_format($todayTotal, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ $todayCount }} HP terjual</p>
            <a href="{{ route('tenant.reports.shifts') }}" class="mt-3 inline-block text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                Lihat rekap shift &rarr;
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan Ini ({{ now()->translatedFormat('F Y') }})</p>
            <p class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                Rp {{ number_format($monthTotal, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-slate-500">{{ $monthCount }} HP terjual</p>
        </div>
    </div>

    {{-- Filter rentang tanggal --}}
    <form method="GET" action="{{ route('tenant.reports') }}"
        class="mt-4 flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="from" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Dari</label>
            <input id="from" name="from" type="date" value="{{ $from?->format('Y-m-d') }}"
                class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div class="flex-1">
            <label for="to" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Sampai</label>
            <input id="to" name="to" type="date" value="{{ $to?->format('Y-m-d') }}"
                class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
            @if ($hasRange)
                <a href="{{ route('tenant.reports') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            @endif
        </div>
    </form>

    @php
        $sections = $hasRange
            ? [['label' => 'Rentang '.($from?->format('d M Y') ?? '…').' — '.($to?->format('d M Y') ?? '…'), 'rows' => $rangeTransactions, 'total' => $rangeTotal, 'count' => $rangeCount]]
            : [
                ['label' => 'Hari Ini — '.now()->format('d M Y'), 'rows' => $todayTransactions, 'total' => $todayTotal, 'count' => $todayCount],
                ['label' => 'Bulan Ini — '.now()->translatedFormat('F Y'), 'rows' => $monthTransactions, 'total' => $monthTotal, 'count' => $monthCount],
            ];
    @endphp

    @foreach ($sections as $section)
        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-slate-900">{{ $section['label'] }}</h2>
                <p class="text-xs text-slate-500">
                    <strong class="text-slate-900">Rp {{ number_format($section['total'], 0, ',', '.') }}</strong>
                    &middot; {{ $section['count'] }} HP
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[42rem] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 font-semibold">Jam</th>
                            <th class="whitespace-nowrap px-4 py-3 font-semibold">HP Terjual</th>
                            <th class="whitespace-nowrap px-4 py-3 font-semibold">IMEI</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right font-semibold">Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($section['rows'] as $trx)
                            <tr class="hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                    {{ $trx->created_at->format('d M H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-900">
                                    {{ $trx->inventory?->product?->tipe_hp ?? '—' }}
                                    <span class="block text-xs font-normal text-slate-500">{{ $trx->inventory?->product?->brand }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">{{ $trx->inventory?->imei }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">
                                    Rp {{ number_format((int) $trx->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Belum ada penjualan pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

</div>
@endsection
