<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
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

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        abort_unless($tenant, 403, 'Akun Anda belum terhubung ke toko.');

        $data = $request->validate([
            'tipe_hp' => ['required', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:80'],
            'price_sell' => ['required', 'integer', 'min:0'],
            'imei' => ['required', 'string'],
            'entry_date' => ['nullable', 'date'],
        ], [
            'imei.required' => 'Minimal isi satu nomor IMEI.',
        ]);

        // Satu field teks bisa berisi banyak IMEI: pisah baris / koma / spasi.
        $imeis = collect(preg_split('/[\s,;]+/', $data['imei']))
            ->map(fn ($v) => preg_replace('/\D/', '', $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values();

        if ($imeis->isEmpty()) {
            return back()->withInput()->withErrors(['imei' => 'Nomor IMEI tidak valid.']);
        }

        // ---- validasi limit total IMEI paket ----
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

                Inventory::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'imei' => $imei,
                    'status' => Inventory::STATUS_AVAILABLE,
                    'entry_date' => $data['entry_date'] ?? now()->toDateString(),
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
