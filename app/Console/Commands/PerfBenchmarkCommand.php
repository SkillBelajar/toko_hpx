<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Benchmark slice 2 (Performance Efficiency).
 * Target standar: cari IMEI di 5.000 data selesai < 2 detik;
 * halaman buka < 3 detik di 3G.
 */
class PerfBenchmarkCommand extends Command
{
    protected $signature = 'perf:bench {--seed : buat 5.000 unit dulu} {--imei= : IMEI yang dicari}';

    protected $description = 'Ukur waktu pencarian IMEI + query list inventory';

    public function handle(): int
    {
        if ($this->option('seed')) {
            $this->call('db:seed', ['--class' => 'Database\Seeders\PerfInventorySeeder', '--force' => true]);
            $this->info('Seed selesai.');
        }

        $tenant = Tenant::where('nama_toko', \Database\Seeders\PerfInventorySeeder::TENANT_NAME)->first();
        if (! $tenant) {
            $this->error('Tenant perf belum ada. Jalankan: php artisan perf:bench --seed');

            return self::FAILURE;
        }

        $count = Inventory::where('tenant_id', $tenant->id)->count();
        $this->info("Inventories tenant perf: {$count}");

        // 1) Pencarian IMEI eksak (yang dipakai scanner di POS)
        $imei = $this->option('imei') ?: Inventory::where('tenant_id', $tenant->id)
            ->where('status', 'available')
            ->value('imei');

        DB::enableQueryLog();
        $t0 = microtime(true);
        $item = Inventory::where('tenant_id', $tenant->id)
            ->where('imei', $imei)
            ->first();
        $ms = (microtime(true) - $t0) * 1000;
        $queries = DB::getQueryLog();

        $this->info(sprintf('lookup IMEI %s: %.1f ms (%d query) found=%s',
            $imei, $ms, count($queries), $item ? 'ya' : 'tidak'));

        // 2) Halaman list inventory (paginate 25 + with('product'))
        DB::flushQueryLog();
        $t0 = microtime(true);
        $page = Inventory::where('tenant_id', $tenant->id)
            ->with('product')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(25);
        $ms = (microtime(true) - $t0) * 1000;

        $this->info(sprintf('halaman list (25 row): %.1f ms (%d query)',
            $ms, count(DB::getQueryLog())));

        // 3) Search LIKE (ketik manual, bukan scanner)
        DB::flushQueryLog();
        $t0 = microtime(true);
        $hits = Inventory::where('tenant_id', $tenant->id)
            ->where('imei', 'like', '3520000000%')
            ->limit(25)
            ->get();
        $ms = (microtime(true) - $t0) * 1000;

        $this->info(sprintf('search LIKE prefix: %.1f ms (%d query, %d hit)',
            $ms, count(DB::getQueryLog()), $hits->count()));

        return self::SUCCESS;
    }
}
