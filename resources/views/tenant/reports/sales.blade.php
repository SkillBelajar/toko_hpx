@extends('tenant.layout')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
            <p class="text-sm text-gray-500">Rekap omzet, keuntungan, serta rincian transaksi tunai & kredit leasing.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.export.sales', request()->all()) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ekspor Excel (.xls)
            </a>
        </div>
    </div>

    <!-- Ringkasan Finansial -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Omzet</p>
            <p class="mt-1 text-xl font-bold text-gray-900">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</p>
            <p class="mt-0.5 text-xs text-gray-500">{{ $totalUnits }} unit terjual</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimasi Laba</p>
            <p class="mt-1 text-xl font-bold text-emerald-600">Rp {{ number_format($totalLaba, 0, ',', '.') }}</p>
            <p class="mt-0.5 text-xs text-gray-500">Total HPP: Rp {{ number_format($totalHpp, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Kredit Leasing</p>
            <p class="mt-1 text-xl font-bold text-purple-600">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
            <p class="mt-0.5 text-xs text-gray-500">DP Terkumpul: Rp {{ number_format($totalDp, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kas Tunai (Cash & DP)</p>
            <p class="mt-1 text-xl font-bold text-blue-600">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
            <p class="mt-0.5 text-xs text-gray-500">QRIS: Rp {{ number_format($totalQris, 0, ',', '.') }} | TF: Rp {{ number_format($totalTransfer, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Filter Bar Lengkap -->
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <form method="GET" action="{{ route('tenant.reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Metode Bayar</label>
                <select name="method" class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ $method === 'cash' ? 'selected' : '' }}>Cash / Tunai</option>
                    <option value="credit" {{ $method === 'credit' ? 'selected' : '' }}>Kredit (Leasing)</option>
                    <option value="qris" {{ $method === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="transfer" {{ $method === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Penyedia Kredit</label>
                <select name="credit_provider" class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Leasing</option>
                    @foreach($creditProviders as $slug => $label)
                        <option value="{{ $slug }}" {{ $creditProvider === $slug ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Status DP</label>
                <select name="dp_status" class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Semua Status DP</option>
                    <option value="dp" {{ $dpFilter === 'dp' ? 'selected' : '' }}>Ada DP</option>
                    <option value="no_dp" {{ $dpFilter === 'no_dp' ? 'selected' : '' }}>Tanpa DP (0 DP)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Cari Nota / IMEI / HP</label>
                <div class="flex gap-2">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Ketik IMEI / Nota..." class="w-full text-sm rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Rincian Transaksi -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu / Nota</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit & IMEI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status Pembayaran</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Harga Jual</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Diskon</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Akhir</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Estimasi Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($transactions as $t)
                        @php
                            $hpp = (int) ($t->price_buy ?? 0);
                            $final = (int) $t->final_price;
                            $laba = $final - $hpp;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">{{ $t->nota_id }}</span>
                                    <a href="{{ route('tenant.pos.nota.print', ['notaId' => $t->nota_id]) }}" target="_blank"
                                       class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-300 transition"
                                       title="Cetak Nota A4">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                        Nota
                                    </a>
                                </div>
                                <div class="text-xs text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $t->brand }} {{ $t->tipe_hp }}</div>
                                <div class="text-xs font-mono text-gray-500">{{ $t->imei }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($t->payment_method === 'credit')
                                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        KREDIT: {{ $creditProviders[$t->credit_provider] ?? ucfirst($t->credit_provider ?? 'Leasing') }}
                                    </div>
                                    <div class="mt-1 text-xs">
                                        @if($t->is_dp && $t->dp_amount > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                DP Rp {{ number_format($t->dp_amount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                Tanpa DP
                                            </span>
                                        @endif
                                    </div>
                                @elseif($t->payment_method === 'cash')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        CASH / TUNAI
                                    </span>
                                @elseif($t->payment_method === 'qris')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        QRIS
                                    </span>
                                @elseif($t->payment_method === 'transfer')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        TRANSFER BANK
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ strtoupper($t->payment_method) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap text-sm text-gray-900">
                                Rp {{ number_format($t->total_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap text-sm text-red-600">
                                {{ $t->discount_amount > 0 ? '-Rp '.number_format($t->discount_amount, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap text-sm font-bold text-gray-900">
                                Rp {{ number_format($final, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap text-sm font-bold {{ $laba >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($laba, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">
                                Tidak ada transaksi yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
