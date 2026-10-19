<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengeluaran multi-item: 1 transaksi (expenses) bisa berisi N baris (expense_items).
     * Kategori tidak dihapus dari DB — hanya disembunyikan dari form manual,
     * karena masih dipakai dashboard/laporan + expense otomatis pembelian/maintenance.
     */
    public function up(): void
    {
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $table->string('uraian');
            $table->integer('qty')->default(1);
            $table->decimal('harga_satuan', 14, 0)->default(0);
            $table->decimal('subtotal', 14, 0)->default(0);
            $table->timestamps();
        });

        // Backfill: tiap expense lama jadi 1 baris item (uraian=keterangan, qty=1)
        foreach (\App\Models\Expense::orderBy('id')->get() as $e) {
            \App\Models\ExpenseItem::create([
                'expense_id' => $e->id,
                'uraian' => $e->keterangan ?: 'Pengeluaran',
                'qty' => 1,
                'harga_satuan' => $e->jumlah,
                'subtotal' => $e->jumlah,
            ]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_items');

        Schema::table('expenses', function (Blueprint $table) {
            $kat = \App\Models\ExpenseCategory::first();
            if ($kat) {
                \App\Models\Expense::whereNull('expense_category_id')->update(['expense_category_id' => $kat->id]);
            }
        });
    }
};
