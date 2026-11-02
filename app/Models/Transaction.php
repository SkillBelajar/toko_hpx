<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'shift_id',
        'nota_id',
        'inventory_id',
        'total_price',
        'discount_amount',
        'discount_type',
        'payment_method',
        'payment_cash',
        'payment_qris',
        'payment_transfer',
        'final_price',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
            'discount_amount' => 'integer',
            'final_price' => 'integer',
            'payment_cash' => 'integer',
            'payment_qris' => 'integer',
            'payment_transfer' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'shift_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /** Harga akhir setelah diskon. */
    public function getFinalPriceAttribute($value): int
    {
        if ($value > 0) {
            return (int) $value;
        }

        return (int) $this->total_price;
    }
}