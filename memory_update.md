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

## PENTING — belum selesai / risiko
1. **Belum rebuild/restart container** — perubahan route/controller butuh `docker restart tokohpx_app`.
2. **Bug di `shift/close.blade.php`**: x-data punya brace typo `..., mode: 'total' }"` (kurung `{` tidak seimbang) — harus diperbaiki sebelum dipakai.
3. **Stray `</div>`** di `shift/close.blade.php` — cek balance div.
4. Belum ada nota/struk **cetak** (rupanya user mau → masih rencana, belum dikerjakan).
5. `standar.txt` (daftar 9 butir ISO 25010) ikut ter-commit dari user.

## Status git
Commit `6c025bf` sudah push ke origin/main (11 file, +652/−312).

## Langkah berikut
- Perbaiki brace typo + stray div di `shift/close.blade.php`.
- Restart container, migrasi ulang, build aset, test checkout multi-item + diskon + metode mixed end-to-end di browser.
