<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('top_ups', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('jumlah', 14, 0)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        foreach (['Air', 'Listrik', 'Internet', 'Gaji', 'Perawatan', 'Kebersihan', 'Lainnya'] as $nama) {
            \App\Models\ExpenseCategory::firstOrCreate(['nama' => $nama]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('top_ups');
    }
};
