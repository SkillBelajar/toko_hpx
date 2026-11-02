<?php

namespace App\Support;

use App\Models\InventoryAudit;

/**
 * Catat siapa mengubah/menghapus/menjual unit stok.
 * Standar #6 (Security): "log siapa ubah stok".
 */
class AuditLogger
{
    /**
     * @param  array<string, array{old: mixed, new: mixed}>  $changes
     */
    public static function log(int $tenantId, string $action, ?int $inventoryId, array $changes = []): void
    {
        try {
            InventoryAudit::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'inventory_id' => $inventoryId,
                'action' => $action,
                'changes' => $changes ?: null,
                'ip' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // audit tidak boleh menggagalkan transaksi utama
            report($e);
        }
    }
}
