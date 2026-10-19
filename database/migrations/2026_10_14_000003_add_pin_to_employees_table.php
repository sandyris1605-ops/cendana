<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PIN disimpan plaintext + unique agar mudah dicek duplikat & dicetak slip untuk lansia.
     * Keamanan dijaga berlapis: token QR lokasi + wajib wajah cocok + wajib dalam radius
     * + rate-limit halaman publik. Halaman admin tetap butuh login — PIN tidak publik.
     */    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('pin', 10)->nullable()->unique()->after('face_descriptor');
        });

        // Karyawan lama otomatis dapat PIN acak 6 digit yang unik.
        $terpakai = Employee::whereNotNull('pin')->pluck('pin')->all();
        foreach (Employee::whereNull('pin')->get() as $e) {
            do {
                $pin = (string) random_int(100000, 999999);
            } while (in_array($pin, $terpakai, true));
            $terpakai[] = $pin;
            $e->update(['pin' => $pin]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
