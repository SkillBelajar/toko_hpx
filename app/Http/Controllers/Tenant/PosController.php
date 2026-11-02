<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CashShift;
use App\Models\Inventory;
use App\Models\Transaction;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        return view('tenant.pos.index', [
            'shift' => $request->user()->activeShift(),
            'stockCount' => Inventory::where('tenant_id', $tenant?->id)->available()->count(),
            'transactionUsed' => $tenant?->transactionUsed() ?? 0,
            'transactionLimit' => $tenant?->transactionLimit(),
            'packageActive' => $tenant?->packageActive() ?? true,
            'expiredDate' => $tenant?->expired_date,
            'waLink' => $this->upgradeWaLink($tenant),
        ]);
    }

    /** Cek IMEI dari scanner: hanya yang masih available. */
    public function lookup(Request $request): JsonResponse
    {
        $imei = preg_replace('/\D/', '', (string) $request->query('imei'));
        $tenant = $request->user()->tenant;

        $item = Inventory::where('tenant_id', $tenant?->id)
            ->with('product')
            ->where('imei', $imei)
            ->first();

        if (! $item) {
            return response()->json(['found' => false, 'imei' => $imei, 'message' => 'IMEI tidak terdaftar di stok toko ini.'], 404);
        }

        if ($item->status !== Inventory::STATUS_AVAILABLE) {
            return response()->json(['found' => false, 'imei' => $imei, 'message' => 'Unit ini sudah terjual.'], 409);
        }

        return response()->json([
            'found' => true,
            'inventory_id' => $item->id,
            'imei' => $item->imei,
            'tipe_hp' => $item->product?->tipe_hp,
            'brand' => $item->product?->brand,
            'price_sell' => (int) ($item->product?->price_sell ?? 0),
            'status' => $item->status,
            'age_days' => $item->ageInDays(),
        ]);
    }

    /**
     * Checkout multi-item dalam satu nota.
     * Validasi: paket aktif + limit transaksi -> lalu lock tiap item dan insert
     * satu baris Transaction per item dengan notan/nama nota_id yang sama.
     * Diskon dibagi proporsional per item agar rekonsiliasi (sum final_price)
     * selalu tepat terhadap total nota.
     */
    public function checkout(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;
        $shift = $request->user()->activeShift();

        if (! $tenant) {
            return $this->blocked('Akun Anda belum terhubung ke toko.', $tenant);
        }

        if (! $shift) {
            return $this->blocked('Shift sudah ditutup. Buka shift dulu.', $tenant);
        }

        if (! $tenant->packageActive()) {
            return $this->blocked(
                'Masa aktif paket Anda sudah habis pada '.$tenant->expired_date?->format('d M Y').'.',
                $tenant
            );
        }

        // (b) limit total transaksi — dihitung terhadap BANYAK item yang akan diinput
        $requested = (int) count((array) ($request->input('items') ?? []));
        if ($tenant->transactionLimitReached() && $requested === 0) {
            return $this->blocked(
                'Limit Total Transaksi Anda sudah habis ('
                .$tenant->transactionUsed().' dari '.$tenant->transactionLimit().').',
                $tenant
            );
        }
        $limit = $tenant->transactionLimit();
        if ($limit !== null && $tenant->transactionUsed() + $requested > $limit) {
            return $this->blocked(
                'Transaksi melebihi limit paket ('.$tenant->transactionUsed().'+'.$requested
                .' > '.$limit.'). Kurangi jumlah item atau upgrade paket.',
                $tenant
            );
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'integer', 'distinct'],
            'discount_type' => ['nullable', 'in:nominal,percent'],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['required', 'in:'.implode(',', CashShift::PAYMENT_METHODS)],
            'payment.cash' => ['nullable', 'integer', 'min:0'],
            'payment.qris' => ['nullable', 'integer', 'min:0'],
            'payment.transfer' => ['nullable', 'integer', 'min:0'],
        ]);

        $discountType = $data['discount_type'] ?? null;
        $discountValue = (int) ($data['discount_value'] ?? 0);

        try {
            $nota = DB::transaction(function () use ($tenant, $shift, $data, $discountType, $discountValue) {
                // Lock semua item sekaligus, cek masih available & milik tenant
                $items = Inventory::where('tenant_id', $tenant->id)
                    ->where('status', Inventory::STATUS_AVAILABLE)
                    ->whereIn('id', $data['items'])
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // semua id harus valid & available
                if (count($items) !== count($data['items'])) {
                    throw new \RuntimeException('Ada unit yang tidak tersedia atau sudah terjual.');
                }

                $gross = (int) $items->sum(fn ($i) => (int) ($i->product?->price_sell ?? 0));
                $discount = $this->calculateDiscount($discountType, $discountValue, $gross);
                $finalTotal = $gross - $discount;

                // Validasi pembayaran: mixed harus pas; single cukup satu kolom
                $method = $data['payment_method'];
                $pay = array_merge(
                    ['cash' => 0, 'qris' => 0, 'transfer' => 0],
                    array_map('intval', $data['payment'] ?? [])
                );
                if ($method === 'mixed') {
                    $totalInput = $pay['cash'] + $pay['qris'] + $pay['transfer'];
                    if ($totalInput !== $finalTotal) {
                        throw new \RuntimeException('Jumlah breakdown pembayaran tidak sama dengan total nota (Rp '.$finalTotal.').');
                    }
                } else {
                    $pay = array_fill_keys(['cash', 'qris', 'transfer'], 0);
                    $pay[$method] = $finalTotal;
                }

                $notaId = Str::upper(Str::random(8));

                // Distribusi diskon proporsional per item, sisa di item terakhir
                $itemIds = $items->keys()->all();
                $allocated = 0;
                $rows = [];
                $priceBy = $items->map(fn ($i) => (int) ($i->product?->price_sell ?? 0));
                foreach (array_keys($itemIds) as $idx) {
                    $price = (int) ($priceBy[$itemIds[$idx]] ?? 0);
                    $isLast = $idx === count($itemIds) - 1;
                    $d = $isLast
                        ? $discount - $allocated
                        : (int) round($discount * ($price / max(1, $gross)));
                    $allocated += $d;

                    $rows[] = [
                        'id' => $itemIds[$idx],
                        'price' => $price,
                        'discount' => max(0, min($d, $price)),
                    ];
                }

                // Simpan semua transaksi
                foreach ($rows as $r) {
                    $item = $items[$r['id']];
                    $item->update(['status' => Inventory::STATUS_SOLD]);
                    AuditLogger::log($tenant->id, 'checkout', $item->id, [
                        'status' => ['old' => Inventory::STATUS_AVAILABLE, 'new' => Inventory::STATUS_SOLD],
                        'nota_id' => ['old' => null, 'new' => $notaId],
                    ]);
                    Transaction::create([
                        'tenant_id' => $tenant->id,
                        'shift_id' => $shift->id,
                        'nota_id' => $notaId,
                        'inventory_id' => $item->id,
                        'total_price' => $r['price'],
                        'discount_amount' => $r['discount'],
                        'discount_type' => $discountType ?? 'nominal',
                        'payment_method' => $method,
                        'payment_cash' => $pay['cash'],
                        'payment_qris' => $pay['qris'],
                        'payment_transfer' => $pay['transfer'],
                        'final_price' => $r['price'] - $r['discount'],
                    ]);
                }

                // Rekonsiliasi laci dari seluruh transaksi shift — cukup 1x setelah semua insert
                $shift->recalculateExpected();

                return [
                    'nota_id' => $notaId,
                    'final_total' => $finalTotal,
                    'gross' => $gross,
                    'discount' => $discount,
                    'count' => count($rows),
                ];
            });
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['ok' => false, 'message' => 'Unit tidak tersedia atau sudah terjual.'], 409);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Transaksi berhasil ('.$nota['count'].' item).',
            'nota_id' => $nota['nota_id'],
            'total_price' => $nota['final_total'],
            'gross' => $nota['gross'],
            'discount' => $nota['discount'],
            'transaction_used' => $tenant->transactionUsed(),
            'transaction_limit' => $tenant->transactionLimit(),
        ]);
    }

    private function calculateDiscount(?string $type, int $value, int $gross): int
    {
        if (! $type || $value <= 0 || $gross <= 0) {
            return 0;
        }

        if ($type === 'percent') {
            return (int) round($gross * min(100, $value) / 100);
        }

        return min($value, $gross);
    }

    private function blocked(string $message, $tenant): JsonResponse
    {
        $upgrade = $tenant?->upgradePackage();

        return response()->json([
            'ok' => false,
            'reason' => 'limit',
            'message' => $message,
            'wa_link' => $this->upgradeWaLink($tenant, $upgrade),
            'wa_text' => $this->upgradeWaText($tenant, $upgrade),
        ], 403);
    }

    private function upgradeWaText($tenant, $upgrade = null): string
    {
        $upgrade ??= $tenant?->upgradePackage();
        $nama = $upgrade?->name ?? 'Pro';

        return "Halo Admin, saya ingin Upgrade ke Paket {$nama}";
    }

    private function upgradeWaLink($tenant, $upgrade = null): string
    {
        $admin = config('services.whatsapp.admin', '089696380422');
        $text = $this->upgradeWaText($tenant, $upgrade);

        return 'https://wa.me/'.preg_replace('/\D/', '', $admin).'?text='.rawurlencode($text);
    }
}
