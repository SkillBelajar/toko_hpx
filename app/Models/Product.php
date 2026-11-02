<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'tenant_id',
        'tipe_hp',
        'brand',
        'price_buy',
        'price_sell',
    ];

    protected function casts(): array
    {
        return [
            'price_buy' => 'integer',
            'price_sell' => 'integer',
        ];
    }

    /** Margin per unit: harga jual - harga modal. */
    public function margin(): ?int
    {
        if ($this->price_buy === null) {
            return null;
        }

        return (int) $this->price_sell - (int) $this->price_buy;
    }

    /** Persentase laba terhadap harga jual; null kalau modal belum diisi. */
    public function marginPercent(): ?float
    {
        if ($this->price_buy === null || ! $this->price_sell) {
            return null;
        }

        return round(($this->price_sell - $this->price_buy) / $this->price_sell * 100, 1);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }
}
