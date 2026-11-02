# Memory Update — Slice: Functional Suitability (ISO 25010)

Tanggal: 2026-11-02 · Repo: SkillBelajar/toko_hpx · Branch: main

## Ringkasan
Saat ini sedang mengerjakan **Slice Functional Suitability** (ISO/IEC 25010:2023 butir 1), target: kasir bisa hitung total 3 barang diskon 10% + IMEI tersimpan tanpa salah hitung.

## Yang sudah di-update (belum diverifikasi penuh di container)

### 1. Database — migration baru
`database/migrations/2026_11_01_000000_enhance_transactions_and_shifts.php`
- `transactions` + kolom: `nota_id` (grup multi-item per struk), `discount_amount`, `discount_type` (nominal|percent), `payment_method` (cash|qris|transfer|mixed), `payment_cash`, `payment_qris`, `payment_transfer`, `final_price`
- `cash_shifts` + kolom: `expected_cash/qris/transfer`, `closing_cash/qris/transfer`
- Sudah dijalankan: `php artisan migrate --force` → DONE.

### 2. Model
- `App\Models\Transaction`: fillable baru + cast integer + `finalPrice` accessor (fallback ke total_price).
- `App\Models\CashShift`: konstanta `PAYMENT_METHODS`, metode `recalculateExpected()` (update expected_amount + breakdown metode dari SUM transaksi final_price), `getTotalSalesAttribute`, variance per metode.

### 3. Controller
- `Tenant\PosController@checkout` — DIREWRITE: terima array `items` (multi IMEI, satu nota), hitung gross → diskon (nominal/persen, kalkulasi proporsional per item, sisa di item terakhir) → final_total; validasi limit paket terhitung `used + jumlah_item`; lock semua item; insert 1 Transaction per item dengan `nota_id` sama; rekonsiliasi shift via `recalculateExpected()`. Response: `nota_id`, `total_price`(final), `gross`, `discount`, `transaction_used/limit`.
- `Tenant\ShiftController`: `close()` tampilkan breakdown expected per metode; `update()` terima `closing_cash/qris/transfer` + `expectedAmount()` pindah ke `sum('final_price')`.
- `Tenant\ReportController`: semua total pindah `total_price` → `final_price`; `withSum('transactions as sales_total', 'final_price')`.

### 4. View
- `tenant/pos/index.blade.php` — REWRITE: keranjang nota multi-item (scan manual/kamera), ringkasan subtotal/diskón/total, pilih metode bayar (Cash/QRIS/Transfer/Campur, mixed = input breakdown 3 kolom), modal konfirmasi upsell full-screen kuning.
- `tenant/pos/_pos.js.blade.php` — BARU: seluruh logika Alpine dipindah ke partial, di `@include` dari index. (Script lama terhapus saat rewrite.)
- `tenant/shift/close.blade.php` — tambah toggle "Total saja / Per metode" + input breakdown 3 kolom + selisih live per metode.

## Status: DONE ✓

### Test results (verified via API)
- Checkout diskon nominal Rp 200rb → `gross=18500000, disc=200000, final=18300000, cash=18300000` ✓
- Checkout diskon 5% → `gross=14500000, disc=725000, final=13775000, mixed cash=8jt+qris=5.775jt` ✓
- SUM(final_price) = 32075000 ✓, SUM(cash)=26300000 ✓, SUM(qris)=5775000 ✓
- shift expected_amount = opening(100rb) + sum(final)=32175000 ✓, expected_cash=26300000 ✓
- Nota ID unik, limit transaksi ter-update, IMEI status→sold ✓

### Bug fixed
- `_pos.js.blade.php` → `pos_js.blade.php` (underscore prefix tidak dibaca Laravel Blade)
- `recalculateExpected()` di luar loop transaksi (sebelumnya tiap insert salah karena row belum ada)
- Mixed payment: reorder `array_fill_keys` sebelum assign `$pay[$method]` (bukan sesudah)
- ShiftController: call `recalculateExpected()` di `close()` sebelum render view
- ShiftController: `$shift->expected_cash/qris/transfer` di `update()` dari attribute model (sudah di-set oleh recalculateExpected())

## Langkah berikut
- Nota/struk cetak → masih rencana (butir Functional Suitability + Compatibility ISO 25010)
