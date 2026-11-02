<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAudit extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'inventory_id', 'action', 'changes', 'ip',
    ];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
}
