<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public const PAYMENT_METHODS = ['cash', 'qris', 'transfer', 'mixed', 'credit'];

    public const CREDIT_PROVIDERS = [
        'akulaku'    => 'Akulaku',
        'kredivo'    => 'Kredivo',
        'avanto'     => 'Avanto',
        'pastfinace' => 'Pastfinace',
        'fif'        => 'FIF',
        'yes_kredit' => 'Yes Kredit',
    ];

    protected $fillable = [
        'tenant_id',
        'shift_id',
        'nota_id',
        'inventory_id',
        'total_price',
        'discount_amount',
        'discount_type',
        'payment_method',
        'credit_provider',
        'dp_amount',
        'is_dp',
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
            'dp_amount' => 'integer',
            'is_dp' => 'boolean',
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

    /** Label pembayaran ramah pengguna */
    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->payment_method === 'credit') {
            $providerLabel = self::CREDIT_PROVIDERS[$this->credit_provider] ?? ucfirst($this->credit_provider ?? 'Kredit');
            $dpLabel = $this->is_dp && $this->dp_amount > 0 
                ? ' (DP Rp ' . number_format($this->dp_amount, 0, ',', '.') . ')' 
                : ' (Tanpa DP)';
            return 'Kredit - ' . $providerLabel . $dpLabel;
        }

        return match ($this->payment_method) {
            'cash'     => 'Cash / Tunai',
            'qris'     => 'QRIS',
            'transfer' => 'Transfer Bank',
            'mixed'    => 'Campur',
            default    => ucfirst($this->payment_method ?? '—'),
        };
    }
}
