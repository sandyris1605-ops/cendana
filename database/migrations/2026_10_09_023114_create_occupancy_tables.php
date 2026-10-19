<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 14, 0)->default(0);
            $table->string('keterangan');
            $table->string('bukti')->nullable();
            $table->timestamps();
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->decimal('gaji', 12, 0)->default(0);
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nik')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat_asal')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->timestamps();
        });
        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->decimal('harga_sewa', 12, 0)->default(0);
            $table->decimal('deposit', 12, 0)->default(0);
            $table->string('status')->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenancies');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
