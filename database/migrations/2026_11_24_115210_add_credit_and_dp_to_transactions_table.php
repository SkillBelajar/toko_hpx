<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah payment_method agar mendukung 'credit'
        // Di MySQL/MariaDB bisa menggunakan alter table modify column
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method VARCHAR(32) NOT NULL DEFAULT 'cash'");

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('credit_provider', 50)->nullable()->after('payment_method')->index();
            $table->unsignedBigInteger('dp_amount')->default(0)->after('credit_provider');
            $table->boolean('is_dp')->default(false)->after('dp_amount')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['credit_provider', 'dp_amount', 'is_dp']);
        });

        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'qris', 'transfer', 'mixed') NOT NULL DEFAULT 'cash'");
    }
};
