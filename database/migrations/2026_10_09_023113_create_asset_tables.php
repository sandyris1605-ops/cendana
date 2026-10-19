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
        Schema::create('asset_units', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barcode')->unique();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->date('tanggal_beli')->nullable();
            $table->decimal('harga_perolehan', 12, 0)->default(0);
            $table->decimal('nilai_residu', 12, 0)->default(0);
            $table->decimal('nilai_buku', 12, 0)->default(0);
            $table->string('kondisi')->default('baik');
            $table->string('status')->default('gudang');
            $table->date('tanggal_ganti_rencana')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
        Schema::create('room_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('asset_unit_id')->constrained('asset_units')->cascadeOnDelete();
            $table->date('tanggal_pasang')->nullable();
            $table->date('tanggal_lepas')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
        Schema::create('depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_unit_id')->constrained('asset_units')->cascadeOnDelete();
            $table->string('periode');
            $table->decimal('nilai_penyusutan', 12, 0)->default(0);
            $table->decimal('nilai_buku_akhir', 12, 0)->default(0);
            $table->timestamps();
            $table->unique(['asset_unit_id','periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciations');
        Schema::dropIfExists('room_assets');
        Schema::dropIfExists('asset_units');
    }
};
