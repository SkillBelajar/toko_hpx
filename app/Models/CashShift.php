<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    public const PAYMENT_METHODS = ['cash', 'qris', 'transfer', 'mixed'];

    protected $fillable = [
        'tenant_id',
        'user_id',
        'opening_amount',
        'closing_amount',
        'expected_amount',
        'variance',
        'opened_at',
        'closed_at',
        'expected_cash',
        'expected_qris',
        'expected_transfer',
        'closing_cash',
        'closing_qris',
        'closing_transfer',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'closing_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'variance' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'expected_cash' => 'integer',
            'expected_qris' => 'integer',
            'expected_transfer' => 'integer',
            'closing_cash' => 'integer',
            'closing_qris' => 'integer',
            'closing_transfer' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'shift_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('closed_at');
    }

    public function isActive(): bool
    {
        return $this->closed_at === null;
    }

    public function getTotalSalesAttribute(): int
    {
        return (int) ($this->transactions()->sum('final_price') ?: 0);
    }

    /** Update expected_amount + breakdown per metode dari transaksi shift ini. */
    public function recalculateExpected(): void
    {
        $agg = $this->transactions()
            ->selectRaw('SUM(total_price) as total, SUM(final_price) as final,
                         SUM(payment_cash) as cash, SUM(payment_qris) as qris, SUM(payment_transfer) as transfer')
            ->first();

        $final = (int) ($agg->final ?: 0);
        $this->update([
            'expected_amount' => (float) ($this->opening_amount + $final),
            'expected_cash' => (int) ($agg->cash ?: 0),
            'expected_qris' => (int) ($agg->qris ?: 0),
            'expected_transfer' => (int) ($agg->transfer ?: 0),
        ]);
    }

    public function varianceAmount(): float
    {
        return (float) ($this->closing_amount ?? 0) - (float) ($this->expected_amount ?? 0);
    }

    public function varianceCash(): int
    {
        return (int) (($this->closing_cash ?? 0) - ($this->expected_cash ?? 0));
    }

    public function varianceQris(): int
    {
        return (int) (($this->closing_qris ?? 0) - ($this->expected_qris ?? 0));
    }

    public function varianceTransfer(): int
    {
        return (int) (($this->closing_transfer ?? 0) - ($this->expected_transfer ?? 0));
    }
}
