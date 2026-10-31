<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('tipe_hp');
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('price_sell')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'tipe_hp']);
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('imei', 32);
            $table->enum('status', ['available', 'sold'])->default('available');
            $table->date('entry_date');
            $table->timestamps();

            // IMEI unik per tenant, bukan global.
            $table->unique(['tenant_id', 'imei']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('products');
    }
};
