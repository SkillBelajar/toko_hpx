<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Validasi ganda sebelum bayar: masa aktif paket + limit total transaksi.
     * Dipakai oleh tombol checkout di UI.
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

        // (a) masa aktif paket
        if (! $tenant->packageActive()) {
            return $this->blocked(
                'Masa aktif paket Anda sudah habis pada '.$tenant->expired_date?->format('d M Y').'.',
                $tenant
            );
        }

        // (b) limit total transaksi
        if ($tenant->transactionLimitReached()) {
            return $this->blocked(
                'Limit Total Transaksi Anda sudah habis ('
                .$tenant->transactionUsed().' dari '.$tenant->transactionLimit().').',
                $tenant
            );
        }

        $data = $request->validate([
            'inventory_id' => ['required', 'integer'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($tenant, $shift, $data) {
                $item = Inventory::where('tenant_id', $tenant->id)
                    ->where('status', Inventory::STATUS_AVAILABLE)
                    ->lockForUpdate()
                    ->findOrFail($data['inventory_id']);

                $item->update(['status' => Inventory::STATUS_SOLD]);

                $trx = Transaction::create([
                    'tenant_id' => $tenant->id,
                    'shift_id' => $shift->id,
                    'inventory_id' => $item->id,
                    'total_price' => (int) ($item->product?->price_sell ?? 0),
                ]);

                // Rekonsiliasi laci: uang tunai masuk menambah expected_amount.
                $shift->update([
                    'expected_amount' => (float) ($shift->expected_amount ?? $shift->opening_amount) + $trx->total_price,
                ]);

                return $trx;
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'ok' => false,
                'message' => 'Unit tidak tersedia atau sudah terjual.',
            ], 409);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Transaksi berhasil.',
            'transaction_id' => $transaction->id,
            'total_price' => $transaction->total_price,
            'transaction_used' => $tenant->transactionUsed(),
            'transaction_limit' => $tenant->transactionLimit(),
        ]);
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
        $admin = config('services.whatsapp.admin', '6281234567890');
        $text = $this->upgradeWaText($tenant, $upgrade);

        return 'https://wa.me/'.preg_replace('/\D/', '', $admin).'?text='.rawurlencode($text);
    }
}
