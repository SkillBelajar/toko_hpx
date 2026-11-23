<?php

namespace Database\Seeders;

use App\Models\CashShift;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantXDummySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::find(1);
        if (!$tenant) {
            $this->command?->error('Tenant ID 1 (akun x) tidak ditemukan.');
            return;
        }

        $user = User::where('tenant_id', 1)->first();
        if (!$user) {
            $this->command?->error('User akun x tidak ditemukan.');
            return;
        }

        // 1. Update profil tenant & user jika masih default
        $tenant->update([
            'nama_toko' => 'X Ponsel Ketapang',
            'no_telp'   => '081234567890',
            'alamat'    => 'Jl. R. Suprapto No. 18, Delta Pawan, Ketapang',
        ]);

        $user->update([
            'name' => 'Owner X Ponsel',
        ]);

        // 2. Daftar produk dummy lengkap dengan modal & harga jual
        $productsData = [
            [
                'brand'      => 'VIVO',
                'tipe_hp'    => 'VIVO Y05 4/64 BLUE',
                'price_buy'  => 1400000,
                'price_sell' => 1699000,
                'stock_avail'=> 8,
                'stock_sold' => 2,
            ],
            [
                'brand'      => 'VIVO',
                'tipe_hp'    => 'VIVO Y28 8/128 GLEAMING ORANGE',
                'price_buy'  => 2100000,
                'price_sell' => 2399000,
                'stock_avail'=> 5,
                'stock_sold' => 1,
            ],
            [
                'brand'      => 'VIVO',
                'tipe_hp'    => 'VIVO V40 LITE 5G 8/256 TITANIUM SILVER',
                'price_buy'  => 3800000,
                'price_sell' => 4299000,
                'stock_avail'=> 4,
                'stock_sold' => 1,
            ],
            [
                'brand'      => 'OPPO',
                'tipe_hp'    => 'OPPO A3X 4/64 NEBULA RED',
                'price_buy'  => 1300000,
                'price_sell' => 1599000,
                'stock_avail'=> 6,
                'stock_sold' => 2,
            ],
            [
                'brand'      => 'OPPO',
                'tipe_hp'    => 'OPPO A60 8/128 RIPPLE BLUE',
                'price_buy'  => 2250000,
                'price_sell' => 2599000,
                'stock_avail'=> 5,
                'stock_sold' => 2,
            ],
            [
                'brand'      => 'OPPO',
                'tipe_hp'    => 'OPPO RENO 12F 8/256 OLIVE GREEN',
                'price_buy'  => 3650000,
                'price_sell' => 4199000,
                'stock_avail'=> 4,
                'stock_sold' => 1,
            ],
            [
                'brand'      => 'REALME',
                'tipe_hp'    => 'REALME NOTE 60 4/64 VOYAGE BLUE',
                'price_buy'  => 1150000,
                'price_sell' => 1399000,
                'stock_avail'=> 7,
                'stock_sold' => 3,
            ],
            [
                'brand'      => 'REALME',
                'tipe_hp'    => 'REALME 13 8/128 PIONEER GREEN',
                'price_buy'  => 2600000,
                'price_sell' => 2999000,
                'stock_avail'=> 5,
                'stock_sold' => 1,
            ],
            [
                'brand'      => 'SAMSUNG',
                'tipe_hp'    => 'SAMSUNG GALAXY A06 4/64 LIGHT BLUE',
                'price_buy'  => 1250000,
                'price_sell' => 1499000,
                'stock_avail'=> 6,
                'stock_sold' => 2,
            ],
            [
                'brand'      => 'SAMSUNG',
                'tipe_hp'    => 'SAMSUNG GALAXY A15 5G 8/256 BLUE BLACK',
                'price_buy'  => 2900000,
                'price_sell' => 3399000,
                'stock_avail'=> 4,
                'stock_sold' => 1,
            ],
            [
                'brand'      => 'XIAOMI',
                'tipe_hp'    => 'REDMI 14C 6/128 STARRY BLUE',
                'price_buy'  => 1500000,
                'price_sell' => 1799000,
                'stock_avail'=> 6,
                'stock_sold' => 2,
            ],
            [
                'brand'      => 'IPHONE',
                'tipe_hp'    => 'IPHONE 13 128GB MIDNIGHT (IBOX)',
                'price_buy'  => 8900000,
                'price_sell' => 9999000,
                'stock_avail'=> 2,
                'stock_sold' => 1,
            ],
        ];

        // 3. Shift kasir untuk mencatat transaksi
        $shift = CashShift::where('tenant_id', 1)->first();
        if (!$shift) {
            $shift = CashShift::create([
                'tenant_id'      => 1,
                'user_id'        => $user->id,
                'opening_amount' => 500000,
                'expected_amount'=> 500000,
                'expected_cash'  => 0,
                'expected_qris'  => 0,
                'expected_transfer'=> 0,
                'opened_at'      => Carbon::now()->subDays(2),
            ]);
        }

        $now = Carbon::now();
        $soldInventories = [];

        foreach ($productsData as $index => $item) {
            $product = Product::firstOrCreate(
                [
                    'tenant_id' => 1,
                    'tipe_hp'   => $item['tipe_hp'],
                ],
                [
                    'brand'      => $item['brand'],
                    'price_buy'  => $item['price_buy'],
                    'price_sell' => $item['price_sell'],
                ]
            );

            // Update harga beli jika belum terisi
            if (!$product->price_buy || $product->price_buy == 0) {
                $product->update([
                    'brand'      => $item['brand'],
                    'price_buy'  => $item['price_buy'],
                    'price_sell' => $item['price_sell'],
                ]);
            }

            // Buat unit IMEI Available
            for ($i = 1; $i <= $item['stock_avail']; $i++) {
                $imei = '86' . str_pad((string)(1000000000000 + ($index * 100) + $i), 13, '0', STR_PAD_LEFT);
                Inventory::firstOrCreate(
                    [
                        'tenant_id' => 1,
                        'imei'      => $imei,
                    ],
                    [
                        'product_id' => $product->id,
                        'status'     => 'available',
                        'entry_date' => $now->copy()->subDays(rand(1, 15))->toDateString(),
                    ]
                );
            }

            // Buat unit IMEI Sold + Transaksi
            for ($s = 1; $s <= $item['stock_sold']; $s++) {
                $soldImei = '86' . str_pad((string)(9000000000000 + ($index * 100) + $s), 13, '0', STR_PAD_LEFT);
                $invSold = Inventory::firstOrCreate(
                    [
                        'tenant_id' => 1,
                        'imei'      => $soldImei,
                    ],
                    [
                        'product_id' => $product->id,
                        'status'     => 'sold',
                        'entry_date' => $now->copy()->subDays(rand(10, 30))->toDateString(),
                    ]
                );

                // Tambahkan transaksi jika belum ada
                $existingTx = Transaction::where('tenant_id', 1)->where('inventory_id', $invSold->id)->first();
                if (!$existingTx) {
                    $methods = ['cash', 'qris', 'transfer'];
                    $method = $methods[($index + $s) % 3];
                    $price = $product->price_sell;

                    $paymentCash = $method === 'cash' ? $price : 0;
                    $paymentQris = $method === 'qris' ? $price : 0;
                    $paymentTf   = $method === 'transfer' ? $price : 0;

                    $tx = Transaction::create([
                        'tenant_id'      => 1,
                        'shift_id'       => $shift->id,
                        'nota_id'        => strtoupper(Str::random(8)),
                        'inventory_id'   => $invSold->id,
                        'total_price'    => $price,
                        'discount_amount'=> 0,
                        'discount_type'  => 'nominal',
                        'payment_method' => $method,
                        'payment_cash'   => $paymentCash,
                        'payment_qris'   => $paymentQris,
                        'payment_transfer'=> $paymentTf,
                        'final_price'    => $price,
                        'created_at'     => $now->copy()->subHours(rand(2, 48)),
                    ]);

                    $soldInventories[] = $tx;
                }
            }
        }

        // Rekap ulang total expected pada shift
        $totalCash = Transaction::where('shift_id', $shift->id)->sum('payment_cash');
        $totalQris = Transaction::where('shift_id', $shift->id)->sum('payment_qris');
        $totalTf   = Transaction::where('shift_id', $shift->id)->sum('payment_transfer');

        $shift->update([
            'expected_cash'    => $totalCash,
            'expected_qris'    => $totalQris,
            'expected_transfer'=> $totalTf,
            'expected_amount'  => (float)$shift->opening_amount + $totalCash,
        ]);
    }
}
