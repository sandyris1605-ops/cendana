<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Descriptor wajah (128 angka => JSON) untuk pengenalan otomatis.
        Schema::table('employees', function (Blueprint $table) {
            $table->longText('face_descriptor')->nullable()->after('status');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->string('status')->default('hadir'); // hadir | terlambat | izin | alpa
            // Bukti geofence
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->unsignedInteger('jarak')->nullable(); // meter dari titik pusat
            $table->boolean('lokasi_ok')->default(true);
            // Snapshot foto (opsional) saat absen
            $table->string('foto_masuk')->nullable();
            $table->string('foto_keluar')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('face_descriptor');
        });
    }
};
