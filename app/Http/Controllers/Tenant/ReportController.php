<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;

        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();
        $method = $request->query('method');
        $creditProvider = $request->query('credit_provider');
        $dpFilter = $request->query('dp_status'); // 'dp', 'no_dp'
        $search = trim((string) $request->query('q', ''));

        $baseQuery = Transaction::where('transactions.tenant_id', $tenantId)
            ->whereBetween('transactions.created_at', [$from, $to])
            ->join('inventories', 'inventories.id', '=', 'transactions.inventory_id')
            ->join('products', 'products.id', '=', 'inventories.product_id');

        if ($method) {
            $baseQuery->where('transactions.payment_method', $method);
        }

        if ($creditProvider) {
            $baseQuery->where('transactions.credit_provider', $creditProvider);
        }

        if ($dpFilter === 'dp') {
            $baseQuery->where('transactions.is_dp', true);
        } elseif ($dpFilter === 'no_dp') {
            $baseQuery->where('transactions.is_dp', false);
        }

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('transactions.nota_id', 'like', "%{$search}%")
                  ->orWhere('inventories.imei', 'like', "%{$search}%")
                  ->orWhere('products.brand', 'like', "%{$search}%")
                  ->orWhere('products.tipe_hp', 'like', "%{$search}%");
            });
        }

        // Summary aggregations
        $summary = (clone $baseQuery)->selectRaw('
            COUNT(transactions.id) as total_units,
            SUM(transactions.final_price) as total_omzet,
            SUM(transactions.total_price) as total_gross,
            SUM(transactions.discount_amount) as total_discount,
            SUM(products.price_buy) as total_hpp,
            SUM(transactions.payment_cash) as total_cash,
            SUM(transactions.payment_qris) as total_qris,
            SUM(transactions.payment_transfer) as total_transfer,
            SUM(CASE WHEN transactions.payment_method = "credit" THEN transactions.final_price ELSE 0 END) as total_credit,
            SUM(transactions.dp_amount) as total_dp
        ')->first();

        $totalOmzet = (int) ($summary->total_omzet ?? 0);
        $totalHpp = (int) ($summary->total_hpp ?? 0);
        $totalLaba = $totalOmzet - $totalHpp;

        $transactions = (clone $baseQuery)
            ->select(
                'transactions.*',
                'inventories.imei',
                'products.brand',
                'products.tipe_hp',
                'products.price_buy',
                'products.price_sell'
            )
            ->orderByDesc('transactions.created_at')
            ->paginate(25)
            ->withQueryString();

        return view('tenant.reports.sales', [
            'from'            => $from->toDateString(),
            'to'              => $to->toDateString(),
            'method'          => $method,
            'creditProvider'  => $creditProvider,
            'dpFilter'        => $dpFilter,
            'search'          => $search,
            'transactions'    => $transactions,
            'totalUnits'      => (int) ($summary->total_units ?? 0),
            'totalOmzet'      => $totalOmzet,
            'totalHpp'        => $totalHpp,
            'totalLaba'       => $totalLaba,
            'totalDiscount'   => (int) ($summary->total_discount ?? 0),
            'totalCash'       => (int) ($summary->total_cash ?? 0),
            'totalQris'       => (int) ($summary->total_qris ?? 0),
            'totalTransfer'   => (int) ($summary->total_transfer ?? 0),
            'totalCredit'     => (int) ($summary->total_credit ?? 0),
            'totalDp'         => (int) ($summary->total_dp ?? 0),
            'creditProviders' => Transaction::CREDIT_PROVIDERS,
        ]);
    }
}
