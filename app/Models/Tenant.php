<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'nama_toko',
        'alamat',
        'no_telp',
        'package_id',
        'expired_date',
    ];

    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** Batas total transaksi paket; null = unlimited. */
    public function transactionLimit(): ?int
    {
        return $this->package?->total_transaction_limit;
    }

    public function transactionUsed(): int
    {
        return $this->transactions()->count();
    }

    public function transactionLimitReached(): bool
    {
        $limit = $this->transactionLimit();

        return $limit !== null && $this->transactionUsed() >= $limit;
    }

    /** Paket masih aktif? (tanpa expired_date dianggap aktif) */
    public function packageActive(): bool
    {
        return $this->expired_date === null || $this->expired_date->isFuture() || $this->expired_date->isToday();
    }

    /** Paket satu tingkat di atas paket sekarang (untuk pesan upgrade WA). */
    public function upgradePackage(): ?Package
    {
        return Package::where('price', '>', (int) ($this->package?->price ?? 0))
            ->orderBy('price')
            ->first();
    }

    /** Batas total IMEI paket; null = unlimited. */
    public function imeiLimit(): ?int
    {
        return $this->package?->total_imei_limit;
    }

    public function imeiUsed(): int
    {
        return $this->inventories()->count();
    }

    public function imeiLimitReached(): bool
    {
        $limit = $this->imeiLimit();

        return $limit !== null && $this->imeiUsed() >= $limit;
    }
}
