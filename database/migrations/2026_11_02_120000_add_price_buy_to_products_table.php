<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Harga modal (HPP) — basis hitung laba rugi. Nullable supaya
            // produk lama tetap valid; toko isi belakangan.
            $table->unsignedBigInteger('price_buy')->nullable()->after('price_sell');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_buy');
        });
    }
};
