<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        $search = trim((string) $request->query('q'));

        $inventories = Inventory::query()
            ->where('tenant_id', $tenant?->id)
            ->with('product')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('imei', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($p) => $p->where('tipe_hp', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('tenant.inventory.index', [
            'tenant' => $tenant,
            'inventories' => $inventories,
            'search' => $search,
            'imeiUsed' => $tenant?->imeiUsed() ?? 0,
            'imeiLimit' => $tenant?->imeiLimit(),
            'imeiLimitReached' => $tenant?->imeiLimitReached() ?? false,
            'agingCount' => Inventory::where('tenant_id', $tenant?->id)
                ->available()
                ->whereDate('entry_date', '<', now()->subDays(Inventory::AGING_DAYS))
                ->count(),
        ]);
    }

    public function edit(Request $request, Inventory $inventory): View|RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_unless($inventory->tenant_id === $tenant?->id, 403);

        return view('tenant.inventory.edit', [
            'inventory' => $inventory->load('product'),
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_unless($inventory->tenant_id === $tenant?->id, 403);

        $data = $request->validate([
            'imei'        => ['required', 'string'],
            'tipe_hp'     => ['required', 'string', 'max:120'],
            'brand'       => ['nullable', 'string', 'max:80'],
            'price_sell'  => ['required', 'integer', 'min:0'],
            'entry_date'  => ['required', 'date'],
        ], [
            'imei.required' => 'Nomor IMEI wajib diisi.',
        ]);

        $imei = preg_replace('/\D/', '', $data['imei']);

        if (strlen($imei) < 6 || strlen($imei) > 17) {
            return back()->withInput()->withErrors(['imei' => 'IMEI harus berupa 6-17 digit angka.']);
        }

        // IMEI unik per tenant.
        $dup = Inventory::where('tenant_id', $tenant?->id)
            ->where('imei', $imei)
            ->whereKeyNot($inventory->id)
            ->exists();

        if ($dup) {
            return back()->withInput()->withErrors(['imei' => 'IMEI ini sudah ada di stok toko Anda.']);
        }

        DB::transaction(function () use ($inventory, $data, $imei) {
            $before = [
                'imei' => $inventory->imei,
                'tipe_hp' => $inventory->product?->tipe_hp,
                'brand' => $inventory->product?->brand,
                'price_sell' => $inventory->product?->price_sell,
                'entry_date' => optional($inventory->entry_date)->toDateString(),
            ];

            if ($inventory->status === Inventory::STATUS_AVAILABLE) {
                $inventory->update(['imei' => $imei]);
            }

            $inventory->update([
                'entry_date' => $data['entry_date'],
            ]);

            $inventory->product->update([
                'tipe_hp'    => $data['tipe_hp'],
                'brand'      => $data['brand'] ?: null,
                'price_sell' => $data['price_sell'],
            ]);

            $after = [
                'imei' => $inventory->imei,
                'tipe_hp' => $data['tipe_hp'],
                'brand' => $data['brand'] ?: null,
                'price_sell' => $data['price_sell'],
                'entry_date' => $data['entry_date'],
            ];
            $diff = collect($after)->filter(fn ($v, $k) => ($before[$k] ?? null) != $v)
                ->map(fn ($v, $k) => ['old' => $before[$k] ?? null, 'new' => $v])->all();
            AuditLogger::log($tenant->id, 'update', $inventory->id, $diff);
        });

        return redirect()
            ->route('tenant.inventory.index')
            ->with('status', 'Stok berhasil diperbarui.');
    }

    public function destroy(Request $request, Inventory $inventory): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_unless($inventory->tenant_id === $tenant?->id, 403);

        $productId = $inventory->product_id;
        $wasAvailable = $inventory->status === Inventory::STATUS_AVAILABLE;

        // Unit yang sudah terjual tidak boleh dihapus: menghapusnya ikut menghapus
        // record transaksi (FK cascade) dan membuat laporan shift/omzet jadi salah.
        if (! $wasAvailable && $inventory->transaction()->exists()) {
            return back()->with('error',
                'Unit ini sudah terjual dan punya riwayat transaksi. '
                .'Hapus hanya berlaku untuk stok yang masih tersedia.'
            );
        }

        DB::transaction(function () use ($inventory, $productId, $tenant) {
            AuditLogger::log($tenant->id, 'delete', $inventory->id, [
                'imei' => ['old' => $inventory->imei, 'new' => null],
            ]);
            $inventory->delete();

            // Hapus product jika tidak punya inventory lagi.
            if (! Inventory::where('product_id', $productId)->exists()) {
                Product::destroy($productId);
            }
        });

        return redirect()
            ->route('tenant.inventory.index')
            ->with('status', 'Stok berhasil dihapus.');
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_unless($tenant, 403, 'Akun Anda belum terhubung ke toko.');

        $data = $request->validate([
            'tipe_hp'    => ['required', 'string', 'max:120'],
            'brand'      => ['nullable', 'string', 'max:80'],
            'price_sell' => ['required', 'integer', 'min:0'],
            'imei'       => ['required', 'string'],
            'entry_date' => ['nullable', 'date'],
        ], [
            'imei.required' => 'Minimal isi satu nomor IMEI.',
        ]);

        $imeis = collect(preg_split('/[\s,;]+/', $data['imei']))
            ->map(fn ($v) => preg_replace('/\D/', '', $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values();

        if ($imeis->isEmpty()) {
            return back()->withInput()->withErrors(['imei' => 'Nomor IMEI tidak valid.']);
        }

        // Hasil scan barcode bisa membawa karakter non-digit; setelah dibersihkan
        // panjang harus wajar — cegah IMEI terpotong diam-diam (mis. "CHK8841" -> "8841").
        $tooShort = $imeis->first(fn ($v) => strlen($v) < 6 || strlen($v) > 17);
        if ($tooShort) {
            return back()->withInput()->withErrors([
                'imei' => 'IMEI harus 6-17 digit angka. Nilai tidak valid: "'.$tooShort.'".',
            ]);
        }

        $limit = $tenant->imeiLimit();
        $used = $tenant->imeiUsed();

        if ($limit !== null && ($used + $imeis->count()) > $limit) {
            return back()->withInput()->with('error',
                'Limit Total Input IMEI Anda sudah habis. Silakan Upgrade Paket. '
                ."(Terpakai {$used} dari {$limit}, mencoba menambah {$imeis->count()}.)"
            );
        }

        $created = DB::transaction(function () use ($tenant, $data, $imeis) {
            $product = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'tipe_hp' => $data['tipe_hp']],
                ['brand' => $data['brand'] ?? null, 'price_sell' => $data['price_sell']],
            );

            $skipped = 0;
            $added = 0;

            foreach ($imeis as $imei) {
                if (Inventory::where('tenant_id', $tenant->id)->where('imei', $imei)->exists()) {
                    $skipped++;

                    continue;
                }

                $item = Inventory::create([
                    'tenant_id'  => $tenant->id,
                    'product_id' => $product->id,
                    'imei'       => $imei,
                    'status'     => Inventory::STATUS_AVAILABLE,
                    'entry_date' => $data['entry_date'] ?? now()->toDateString(),
                ]);

                AuditLogger::log($tenant->id, 'create', $item->id, [
                    'imei' => ['old' => null, 'new' => $imei],
                ]);

                $added++;
            }

            return compact('added', 'skipped');
        });

        $message = "{$created['added']} IMEI ditambahkan.";
        if ($created['skipped'] > 0) {
            $message .= " {$created['skipped']} IMEI dilewati (sudah terdaftar).";
        }

        return redirect()->route('tenant.inventory.index')->with('status', $message);
    }
}