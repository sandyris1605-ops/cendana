<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus total fitur absensi: tabel attendances, kolom employees.face_descriptor
     * & employees.pin, serta baris settings absen_*.
     */
    public function up(): void
    {
        Schema::dropIfExists('attendances');

        if (Schema::hasColumn('employees', 'face_descriptor')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('face_descriptor');
            });
        }

        if (Schema::hasColumn('employees', 'pin')) {
            // Hapus index unik PIN bila masih ada (nama bisa berbeda antar DB).
            try {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropUnique(['pin']);
                });
            } catch (\Throwable $e) {
            }
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('pin');
            });
        }

        DB::table('settings')->whereIn('key', [
            'absen_lat', 'absen_lng', 'absen_radius', 'absen_aktif',
            'absen_batas_masuk', 'absen_jam_pulang', 'absen_qr_token',
        ])->delete();
    }

    public function down(): void
    {
        // Tidak dikembalikan — fitur absensi dihapus permanen.
    }
};
