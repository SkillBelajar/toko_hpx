<?php
// Ceklis otomatis toko_hpx — versi PHP (hindari masalah escaping shell).
require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pass = 0; $fail = 0; $lines = [];
function chk(string $name, callable $fn) {
    global $pass, $fail, $lines;
    try {
        if ($fn()) { $lines[] = "[OK]   $name"; $GLOBALS['pass']++; }
        else { $lines[] = "[FAIL] $name"; $GLOBALS['fail']++; }
    } catch (\Throwable $e) {
        $lines[] = "[FAIL] $name — " . substr($e->getMessage(), 0, 120); $GLOBALS['fail']++;
    }
}
function perfUser(): ?App\Models\User {
    return App\Models\User::where('email', 'perf@toko.test')->first();
}
function authPerf() { Illuminate\Support\Facades\Auth::login(perfUser()); }

/**
 * Buat request yang selalu me-resolve user perf — controller memakai
 * $request->user()->tenant, jadi resolver wajib dipasang.
 */
function reqPerf(string $method, string $uri, array $params = []): Illuminate\Http\Request {
    $r = Illuminate\Http\Request::create($uri, $method, $params);
    $r->setUserResolver(fn () => perfUser());
    return $r;
}

echo "=== 1. INFRA ===\n";
chk('app bootstrap', fn () => true);
chk('redis PONG', function () {
    return Illuminate\Support\Facades\Redis::connection()->ping() === true
        || Illuminate\Support\Facades\Redis::connection()->ping() === 'PONG';
});
chk('semua migration Ran', function () {
    Illuminate\Support\Facades\Artisan::call('migrate:status');
    return str_contains(Illuminate\Support\Facades\Artisan::output(), 'Pending') === false;
});

echo "=== 2. SKEMA ===\n";
chk('inventory_audits + changes', fn () => Schema::hasTable('inventory_audits') && Schema::hasColumn('inventory_audits', 'changes'));
chk('transactions final_price + nota_id', fn () => Schema::hasColumn('transactions', 'final_price') && Schema::hasColumn('transactions', 'nota_id'));
chk('cash_shifts breakdown', fn () => Schema::hasColumn('cash_shifts', 'expected_cash') && Schema::hasColumn('cash_shifts', 'expected_qris'));

echo "=== 3. SHIFT PERF AKTIF ===\n";
$u = perfUser();
if ($u && ! $u->activeShift()) {
    App\Models\CashShift::create(['tenant_id' => $u->tenant_id, 'user_id' => $u->id,
        'opening_amount' => 100000, 'expected_amount' => 100000, 'opened_at' => now()]);
}
chk('shift aktif', fn () => perfUser()?->activeShift() !== null);

echo "=== 4. KASIR ===\n";
chk('lookup IMEI', function () {
    authPerf();
    $imei = App\Models\Inventory::where('tenant_id', perfUser()->tenant_id)->available()->value('imei');
    $r = Illuminate\Http\Request::create('/tenant/pos/lookup', 'GET', ['imei' => $imei]);
    $r->setUserResolver(fn () => perfUser());
    $res = app(App\Http\Controllers\Tenant\PosController::class)->lookup($r);
    return ($res->getData()->found ?? false) === true;
});
chk('checkout 2 item diskon 10% (proporsional)', function () {
    authPerf();
    $ids = App\Models\Inventory::where('tenant_id', perfUser()->tenant_id)->available()->take(2)->pluck('id')->all();
    if (count($ids) < 2) return false;
    $r = Illuminate\Http\Request::create('/tenant/pos/checkout', 'POST',
        ['items' => $ids, 'discount_type' => 'percent', 'discount_value' => 10, 'payment_method' => 'cash']);
    $r->setUserResolver(fn () => perfUser());
    $res = app(App\Http\Controllers\Tenant\PosController::class)->checkout($r);
    $d = $res->getData();
    if (! ($d->ok ?? false)) return false;
    return (int) App\Models\Transaction::where('nota_id', $d->nota_id)->sum('final_price') === (int) $d->total_price;
});
chk('checkout mixed tidak pas -> 422', function () {
    authPerf();
    $id = App\Models\Inventory::where('tenant_id', perfUser()->tenant_id)->available()->value('id');
    $r = Illuminate\Http\Request::create('/tenant/pos/checkout', 'POST',
        ['items' => [$id], 'payment_method' => 'mixed', 'payment' => ['cash' => 1, 'qris' => 0, 'transfer' => 0]]);
    $r->setUserResolver(fn () => perfUser());
    return app(App\Http\Controllers\Tenant\PosController::class)->checkout($r)->getStatusCode() === 422;
});
chk('unit terjual tidak bisa dijual lagi', function () {
    authPerf();
    $id = App\Models\Inventory::where('tenant_id', perfUser()->tenant_id)->available()->value('id');
    $r = Illuminate\Http\Request::create('/tenant/pos/checkout', 'POST', ['items' => [$id], 'payment_method' => 'cash']);
    $r->setUserResolver(fn () => perfUser());
    app(App\Http\Controllers\Tenant\PosController::class)->checkout($r)->getStatusCode();
    $r2 = clone $r; $r2->setUserResolver(fn () => perfUser());
    return app(App\Http\Controllers\Tenant\PosController::class)->checkout($r2)->getStatusCode() === 422;
});
chk('audit trail checkout tercatat', fn () => App\Models\InventoryAudit::where('action', 'checkout')->exists());

echo "=== 5. TAMBAH STOK ===\n";
chk('store 3 IMEI sekaligus', function () {
    authPerf();
    $m = (string) random_int(350000000000000, 359999999999999);
    app(App\Http\Controllers\Tenant\InventoryController::class)->store(reqPerf('POST', '/tenant/inventory',
        ['tipe_hp' => 'Ceklis Auto', 'brand' => 'SB', 'price_sell' => 1000000,
            'imei' => $m . "1\n" . $m . "2\n" . $m . "3"]));
    return App\Models\Inventory::where('imei', 'like', $m . '%')->count() === 3;
});
chk('IMEI non-digit terpotong ditolak', function () {
    authPerf();
    $res = app(App\Http\Controllers\Tenant\InventoryController::class)->store(reqPerf('POST', '/tenant/inventory',
        ['tipe_hp' => 'Ceklis Buruk', 'brand' => 'SB', 'price_sell' => 1000000, 'imei' => 'CHK8841']));
    return $res->getStatusCode() === 302
        && App\Models\Inventory::where('imei', '8841')->doesntExist();
});
chk('IMEI duplikat ditolak', function () {
    authPerf();
    $imei = App\Models\Inventory::where('tenant_id', perfUser()->tenant_id)->value('imei');
    $res = app(App\Http\Controllers\Tenant\InventoryController::class)->store(reqPerf('POST', '/tenant/inventory',
        ['tipe_hp' => 'Ceklis Dup', 'brand' => 'SB', 'price_sell' => 1000000, 'imei' => $imei]));
    return $res->getStatusCode() === 302;
});

echo "=== 6. VIEW ===\n";
chk('welcome render', fn () => strlen(view('welcome', ['landingPackages' => collect()])->render()) > 1000);
chk('privacy render', fn () => strlen(view('privacy')->render()) > 1000);
chk('pos render', function () {
    authPerf();
    $r = Illuminate\Http\Request::create('/tenant/pos', 'GET'); $r->setUserResolver(fn () => perfUser());
    return strlen(app(App\Http\Controllers\Tenant\PosController::class)->index($r)->render()) > 1000;
});
chk('laporan sales render', function () {
    authPerf();
    $r = Illuminate\Http\Request::create('/tenant/reports', 'GET'); $r->setUserResolver(fn () => perfUser());
    return strlen(app(App\Http\Controllers\Tenant\ReportController::class)->sales($r)->render()) > 1000;
});

echo "=== 7. ASET & FILE ===\n";
chk('manifest.webmanifest', fn () => file_exists('/var/www/html/public/manifest.webmanifest'));
chk('ikon SVG', fn () => file_exists('/var/www/html/public/icons/icon.svg'));
chk('vite build', fn () => file_exists('/var/www/html/public/build/manifest.json'));

echo "=== 8. HTTP (nginx) ===\n";
chk('GET / 200', fn () => str_starts_with(@file_get_contents('http://web/') !== false ? 'ok' : '', 'ok'));
chk('GET /privasi 200', fn () => @file_get_contents('http://web/privasi') !== false);
chk('manifest MIME manifest+json', function () {
    $h = @get_headers('http://web/manifest.webmanifest', 1) ?: [];
    return stripos(implode(' ', (array) $h), 'application/manifest+json') !== false;
});

echo "=== 9. TEST SUITE ===\n";
chk('phpunit PosCheckoutTest', function () {
    Illuminate\Support\Facades\Artisan::call('test', ['--filter' => 'PosCheckoutTest']);
    return str_contains(Illuminate\Support\Facades\Artisan::output(), 'PASS');
});

echo "\n" . implode("\n", $lines) . "\n";
echo "\n=== RINGKASAN: PASS=$pass FAIL=$fail ===\n";
echo $fail === 0 ? "SEMUA LOLOS\n" : "ADA YANG GAGAL\n";
