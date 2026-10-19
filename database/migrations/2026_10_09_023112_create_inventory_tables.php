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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori')->nullable();
            $table->string('merek')->nullable();
            $table->string('satuan')->default('pcs');
            $table->decimal('harga_estimasi', 12, 0)->default(0);
            $table->integer('umur_pakai_bulan')->default(12);
            $table->integer('stok_minimum')->default(1);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kontak')->nullable();
            $table->string('telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('total', 14, 0)->default(0);
            $table->string('status')->default('selesai');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->decimal('harga_satuan', 12, 0)->default(0);
            $table->decimal('subtotal', 14, 0)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('items');
    }
};
