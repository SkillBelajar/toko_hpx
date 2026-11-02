<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder volume: 5.000 unit inventory untuk satu tenant perf-test.
 * Dipakai mengukur target standar #2 (cari IMEI di 5.000 data < 2 detik).
 */
class PerfInventorySeeder extends Seeder
{
    public const TENANT_NAME = 'Toko Perf';
    public const TENANT_EMAIL = 'perf@toko.test';

    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['nama_toko' => self::TENANT_NAME],
            [
                'alamat' => 'Jl. Perf',
                'no_telp' => '08000000000',
                'expired_date' => now()->addYear(),
            ]
        );

        $user = User::firstOrCreate(
            ['email' => self::TENANT_EMAIL],
            [
                'name' => 'Kasir Perf',
                'password' => 'secret123',
                'role' => User::ROLE_TENANT,
                'tenant_id' => $tenant->id,
            ]
        );
        $user->tenant()->associate($tenant)->save();

        DB::transaction(function () use ($tenant) {
            // idempoten: bersihkan data perf lama
            Product::where('tenant_id', $tenant->id)->where('brand', 'PerfBrand')->delete();

            // 20 produk, tiap produk 250 unit = 5.000 baris
            $products = [];
            for ($p = 1; $p <= 20; $p++) {
                $products[] = Product::create([
                    'tenant_id' => $tenant->id,
                    'tipe_hp' => sprintf('Model Perf %02d', $p),
                    'brand' => 'PerfBrand',
                    'price_sell' => 1_000_000 + ($p * 100_000),
                ]);
            }

            $rows = [];
            $now = now();
            foreach ($products as $i => $product) {
                for ($u = 1; $u <= 250; $u++) {
                    $rows[] = [
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        // IMEI 15 digit, tenant-owned, unik
                        'imei' => sprintf('352%011d', ($i * 250) + $u),
                        'status' => 'available',
                        'entry_date' => $now->toDateString(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            // chunk insert: 1 query per 500 baris, bukan 5.000 query
            foreach (array_chunk($rows, 500) as $chunk) {
                Inventory::insert($chunk);
            }
        });
    }
}
