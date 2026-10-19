<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistem dibiayai donatur: cabut modul tagihan/sewa.
     * Backup CSV sudah disimpan di storage/backups/ sebelum migrate.
     */
    public function up(): void
    {
        Schema::dropIfExists('rent_payments');

        Schema::table('tenancies', function (Blueprint $table) {
            if (Schema::hasColumn('tenancies', 'harga_sewa')) $table->dropColumn('harga_sewa');
            if (Schema::hasColumn('tenancies', 'deposit')) $table->dropColumn('deposit');
        });

        Schema::table('room_types', function (Blueprint $table) {
            if (Schema::hasColumn('room_types', 'harga_bulanan')) $table->dropColumn('harga_bulanan');
        });
    }

    public function down(): void
    {
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('tenancy_id')->constrained('tenancies')->cascadeOnDelete();
            $table->string('periode');
            $table->date('tanggal');
            $table->decimal('jumlah', 12, 0)->default(0);
            $table->string('status')->default('lunas');
            $table->string('metode')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::table('tenancies', function (Blueprint $table) {
            if (!Schema::hasColumn('tenancies', 'harga_sewa')) $table->decimal('harga_sewa', 12, 0)->default(0);
            if (!Schema::hasColumn('tenancies', 'deposit')) $table->decimal('deposit', 12, 0)->default(0);
        });

        Schema::table('room_types', function (Blueprint $table) {
            if (!Schema::hasColumn('room_types', 'harga_bulanan')) $table->decimal('harga_bulanan', 12, 0)->default(0);
        });
    }
};
