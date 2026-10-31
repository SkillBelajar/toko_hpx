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
