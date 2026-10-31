<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('cash_shifts')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->unsignedBigInteger('total_price')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->unique('inventory_id'); // satu unit HP hanya bisa terjual sekali
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
