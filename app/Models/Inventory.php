<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_SOLD = 'sold';

    /** Ambang batas aging alert (hari). */
    public const AGING_DAYS = 30;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'imei',
        'status',
        'entry_date',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /** Umur stok dalam hari. */
    public function ageInDays(): int
    {
        return (int) $this->entry_date->diffInDays(now()->startOfDay());
    }

    /** Stok mengendap: masih available dan umur > AGING_DAYS. */
    public function isAging(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->ageInDays() > self::AGING_DAYS;
    }
}
