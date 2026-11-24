<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Ekspor daftar stok / laporan penjualan ke Excel.
     * Format: SpreadsheetML 2003 (.xls) — dibuka Excel, LibreOffice, Google Sheets.
     * Tanpa dependensi tambahan (standar #7 Compatibility).
     */
    public function inventory(Request $request): StreamedResponse
    {
        $tenant = $request->user()->tenant;
        $search = trim((string) $request->query('q'));
        $brand = trim((string) $request->query('brand'));

        $rows = Inventory::query()
            ->where('tenant_id', $tenant?->id)
            ->with('product')
            ->when($brand !== '', function ($q) use ($brand) {
                $q->whereHas('product', fn ($p) => $p->where('brand', $brand));
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('imei', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($p) => $p->where('tipe_hp', 'like', "%{$search}%")->orWhere('brand', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($rows, $tenant) {
            $cells = [
                ['Tipe HP', 'Brand', 'IMEI', 'Status', 'Harga Jual', 'Tanggal Masuk'],
            ];
            foreach ($rows as $i) {
                $cells[] = [
                    $i->product?->tipe_hp,
                    $i->product?->brand,
                    '="' . $i->imei . '"', // paksa teks agar Excel tidak bulatkan jadi eksponensial
                    $i->status,
                    (int) ($i->product?->price_sell ?? 0),
                    optional($i->entry_date)->format('Y-m-d'),
                ];
            }
            echo ExportController::xml($cells, 'Stok ' . ($tenant->nama_toko ?? 'Toko'));
        }, 'stok-imei-' . now()->format('Ymd-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function sales(Request $request): StreamedResponse
    {
        $tenant = $request->user()->tenant;

        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $method = $request->query('method');
        $creditProvider = $request->query('credit_provider');
        $dpFilter = $request->query('dp_status');
        $search = trim((string) $request->query('q', ''));

        $q = Transaction::query()
            ->where('transactions.tenant_id', $tenant?->id)
            ->with(['inventory.product']);

        if ($from) $q->where('transactions.created_at', '>=', $from);
        if ($to) $q->where('transactions.created_at', '<=', $to);

        if ($method) {
            $q->where('transactions.payment_method', $method);
        }

        if ($creditProvider) {
            $q->where('transactions.credit_provider', $creditProvider);
        }

        if ($dpFilter === 'dp') {
            $q->where('transactions.is_dp', true);
        } elseif ($dpFilter === 'no_dp') {
            $q->where('transactions.is_dp', false);
        }

        if ($search !== '') {
            $q->where(function ($sub) use ($search) {
                $sub->where('transactions.nota_id', 'like', "%{$search}%")
                    ->orWhereHas('inventory', function ($inv) use ($search) {
                        $inv->where('imei', 'like', "%{$search}%")
                            ->orWhereHas('product', fn ($p) => $p->where('brand', 'like', "%{$search}%")->orWhere('tipe_hp', 'like', "%{$search}%"));
                    });
            });
        }

        $rows = $q->latest('transactions.created_at')->get();

        return response()->streamDownload(function () use ($rows) {
            $cells = [
                ['Waktu', 'Nota', 'Tipe HP', 'Brand', 'IMEI', 'Harga Jual', 'Diskon', 'Total Akhir', 'Metode', 'Leasing / Kredit', 'Keterangan DP', 'Nominal DP'],
            ];
            foreach ($rows as $t) {
                $providerName = $t->credit_provider ? (Transaction::CREDIT_PROVIDERS[$t->credit_provider] ?? ucfirst($t->credit_provider)) : '-';
                $dpKet = '-';
                if ($t->payment_method === 'credit') {
                    $dpKet = $t->is_dp ? 'Ada DP' : 'Tanpa DP';
                }

                $cells[] = [
                    $t->created_at->format('Y-m-d H:i'),
                    $t->nota_id,
                    $t->inventory?->product?->tipe_hp,
                    $t->inventory?->product?->brand,
                    '="' . ($t->inventory?->imei ?? '') . '"',
                    (int) $t->total_price,
                    (int) $t->discount_amount,
                    (int) $t->final_price,
                    strtoupper($t->payment_method),
                    $providerName,
                    $dpKet,
                    (int) ($t->dp_amount ?? 0),
                ];
            }
            echo ExportController::xml($cells, 'Penjualan');
        }, 'penjualan-' . now()->format('Ymd-His') . '.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    /** SpreadsheetML 2003 XML minimal. */
    public static function xml(array $rows, string $sheetName): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<?mso-application progid="Excel.Sheet"?>' . "\n"
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            . '<Styles><Style ss:ID="h"><Font ss:Bold="1"/></Style></Styles>'
            . '<Worksheet ss:Name="' . htmlspecialchars(mb_substr($sheetName, 0, 31)) . '"><Table>';

        foreach ($rows as $r) {
            $out .= '<Row>';
            foreach ($r as $i => $v) {
                $style = ($r === $rows[0]) ? ' ss:StyleID="h"' : '';
                if (is_int($v)) {
                    $out .= '<Cell' . $style . '><Data ss:Type="Number">' . $v . '</Data></Cell>';
                } else {
                    $out .= '<Cell' . $style . '><Data ss:Type="String">' . htmlspecialchars((string) ($v ?? '')) . '</Data></Cell>';
                }
            }
            $out .= '</Row>';
        }

        return $out . '</Table></Worksheet></Workbook>';
    }
}
