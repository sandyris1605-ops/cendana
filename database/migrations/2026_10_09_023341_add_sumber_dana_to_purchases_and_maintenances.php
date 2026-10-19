<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('sumber_dana', 20)->default('kas')->after('status');
            $table->foreignId('expense_id')->nullable()->after('sumber_dana')->constrained('expenses')->nullOnDelete();
        });
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('sumber_dana', 20)->default('kas')->after('biaya');
            $table->foreignId('expense_id')->nullable()->after('sumber_dana')->constrained('expenses')->nullOnDelete();
        });

        \App\Models\ExpenseCategory::firstOrCreate(['nama' => 'Pembelian']);
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_id');
            $table->dropColumn('sumber_dana');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_id');
            $table->dropColumn('sumber_dana');
        });
    }
};
