<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CashShift;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Laporan penjualan harian & bulanan + filter rentang tanggal. */
    public function sales(Request $request): View
    {
        $tenant = $request->user()->tenant;
        $tenantId = $tenant?->id;

        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();

        $hasRange = $from !== null || $to !== null;

        $base = fn () => Transaction::query()
            ->where('transactions.tenant_id', $tenantId)
            ->with(['inventory.product'])
            ->when($from, fn ($q) => $q->where('transactions.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('transactions.created_at', '<=', $to));

        $range = (clone $base())->latest('transactions.created_at')->get();

        // Modal tiap baris transaksi: harga beli produk saat itu.
        // Produk tanpa price_buy -> modal dihitung 0, ditandai lewat incompleteCost.
        $cost = fn ($tx) => (int) ($tx->inventory?->product?->price_buy ?? 0);

        $todayRows = (clone $base())
            ->whereDate('transactions.created_at', today())
            ->latest('transactions.created_at')
            ->get();

        $monthRows = (clone $base())
            ->whereYear('transactions.created_at', now()->year)
            ->whereMonth('transactions.created_at', now()->month)
            ->latest('transactions.created_at')
            ->get();

        return view('tenant.reports.sales', [
            'from' => $from,
            'to' => $to,
            'hasRange' => $hasRange,
            'rangeTransactions' => $range,
            'rangeTotal' => (int) $range->sum('final_price'),
            'rangeCost' => (int) $range->sum($cost),
            'rangeProfit' => (int) $range->sum(fn ($tx) => $tx->final_price - $cost($tx)),
            'rangeCount' => $range->count(),
            'incompleteCost' => $range->filter(fn ($tx) => $tx->inventory?->product?->price_buy === null)->count(),

            'todayTransactions' => $todayRows,
            'todayTotal' => (int) $todayRows->sum('final_price'),
            'todayCost' => (int) $todayRows->sum($cost),
            'todayProfit' => (int) $todayRows->sum(fn ($tx) => $tx->final_price - $cost($tx)),
            'todayCount' => $todayRows->count(),

            'monthTransactions' => $monthRows,
            'monthTotal' => (int) $monthRows->sum('final_price'),
            'monthCost' => (int) $monthRows->sum($cost),
            'monthProfit' => (int) $monthRows->sum(fn ($tx) => $tx->final_price - $cost($tx)),
            'monthCount' => $monthRows->count(),
        ]);
    }

    /** Histori shift + selisih kas (anti-maling). */
    public function shifts(Request $request): View
    {
        $tenant = $request->user()->tenant;

        $shifts = CashShift::query()
            ->where('tenant_id', $tenant?->id)
            ->with('user')
            ->withCount('transactions')
            ->withSum('transactions as sales_total', 'final_price')
            ->orderByDesc('opened_at')
            ->paginate(20);

        return view('tenant.reports.shifts', [
            'shifts' => $shifts,
            'shiftsWithVariance' => CashShift::where('tenant_id', $tenant?->id)
                ->whereNotNull('variance')
                ->where('variance', '<', 0)
                ->count(),
        ]);
    }
}
