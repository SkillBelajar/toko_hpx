<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // grup nota: beberapa item = 1 transaksi/struk
            $table->string('nota_id')->nullable()->after('shift_id')->index();
            // diskon
            $table->unsignedInteger('discount_amount')->default(0)->after('total_price');
            $table->enum('discount_type', ['nominal', 'percent'])->default('nominal')->after('discount_amount');
            // metode bayar
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'mixed'])->default('cash')->after('discount_type');
            // breakdown per metode (untuk mixed)
            $table->unsignedBigInteger('payment_cash')->default(0)->after('payment_method');
            $table->unsignedBigInteger('payment_qris')->default(0)->after('payment_cash');
            $table->unsignedBigInteger('payment_transfer')->default(0)->after('payment_qris');
            // harga akhir setelah diskon
            $table->unsignedBigInteger('final_price')->default(0)->after('payment_transfer');
        });

        Schema::table('cash_shifts', function (Blueprint $table) {
            // breakdown per metode bayar
            $table->unsignedBigInteger('expected_cash')->default(0)->after('expected_amount');
            $table->unsignedBigInteger('expected_qris')->default(0)->after('expected_cash');
            $table->unsignedBigInteger('expected_transfer')->default(0)->after('expected_qris');
            $table->unsignedBigInteger('closing_cash')->nullable()->after('closing_amount');
            $table->unsignedBigInteger('closing_qris')->nullable()->after('closing_cash');
            $table->unsignedBigInteger('closing_transfer')->nullable()->after('closing_qris');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['nota_id', 'discount_amount', 'discount_type', 'payment_method', 'payment_cash', 'payment_qris', 'payment_transfer', 'final_price']);
        });
        Schema::table('cash_shifts', function (Blueprint $table) {
            $table->dropColumn(['expected_cash', 'expected_qris', 'expected_transfer', 'closing_cash', 'closing_qris', 'closing_transfer']);
        });
    }
};
