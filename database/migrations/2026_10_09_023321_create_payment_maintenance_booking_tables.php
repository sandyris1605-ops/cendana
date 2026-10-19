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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('prioritas')->default('sedang');
            $table->string('status')->default('baru');
            $table->decimal('biaya', 12, 0)->default(0);
            $table->string('teknisi')->nullable();
            $table->date('tanggal')->nullable();
            $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon');
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->nullOnDelete();
            $table->date('tanggal_rencana')->nullable();
            $table->text('pesan')->nullable();
            $table->string('status')->default('baru');
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi');
            $table->string('modul')->nullable();
            $table->text('detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('rent_payments');
    }
};
