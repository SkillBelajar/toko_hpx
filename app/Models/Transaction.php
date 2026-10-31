<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'shift_id',
        'inventory_id',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'integer',
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
}
