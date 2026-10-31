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

        return view('tenant.reports.sales', [
            'from' => $from,
            'to' => $to,
            'hasRange' => $hasRange,
            'rangeTransactions' => $range,
            'rangeTotal' => (int) $range->sum('total_price'),
            'rangeCount' => $range->count(),

            'todayTransactions' => (clone $base())
                ->whereDate('transactions.created_at', today())
                ->latest('transactions.created_at')
                ->get(),
            'todayTotal' => (int) Transaction::where('tenant_id', $tenantId)->whereDate('created_at', today())->sum('total_price'),
            'todayCount' => Transaction::where('tenant_id', $tenantId)->whereDate('created_at', today())->count(),

            'monthTransactions' => (clone $base())
                ->whereYear('transactions.created_at', now()->year)
                ->whereMonth('transactions.created_at', now()->month)
                ->latest('transactions.created_at')
                ->get(),
            'monthTotal' => (int) Transaction::where('tenant_id', $tenantId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('total_price'),
            'monthCount' => Transaction::where('tenant_id', $tenantId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
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
            ->withSum('transactions as sales_total', 'total_price')
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
