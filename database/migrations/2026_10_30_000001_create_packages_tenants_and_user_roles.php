<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('total_transaction_limit')->nullable(); // null = unlimited
            $table->unsignedInteger('total_imei_limit')->nullable();        // null = unlimited
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nama_toko');
            $table->string('alamat')->nullable();
            $table->string('no_telp', 32)->nullable();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->date('expired_date')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superadmin', 'tenant'])->default('tenant')->after('password');
            $table->foreignId('tenant_id')->nullable()->after('role')->constrained('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn('role');
        });

        Schema::dropIfExists('tenants');
        Schema::dropIfExists('packages');
    }
};
