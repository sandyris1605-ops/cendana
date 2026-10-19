<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->nullable()->after('kategori');
            $table->foreign('kategori_id')->references('id')->on('categories')->onDelete('cascade');
        });

        // Pindahkan kategori bebas lama ke tabel categories
        $map = [
            'Furniture' => 'Furnitur',
            'Elektronik' => 'Elektronik',
            'Aksesori' => 'Aksesori',
            'Elektrikal' => 'Elektrikal',
        ];

        foreach ($map as $old => $new) {
            $catId = DB::table('categories')->insertGetId([
                'nama' => $new,
                'deskripsi' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('items')->where('kategori', $old)->update(['kategori_id' => $catId]);
        }

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->string('kategori')->nullable()->after('id');
        });

        $map = [
            'Furnitur' => 'Furniture',
            'Elektronik' => 'Elektronik',
            'Aksesori' => 'Aksesori',
            'Elektrikal' => 'Elektrikal',
        ];

        foreach (DB::table('categories')->get() as $c) {
            if (isset($map[$c->nama])) {
                DB::table('items')->where('kategori_id', $c->id)->update(['kategori' => $map[$c->nama]]);
            }
        }

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('kategori_id');
        });

        Schema::dropIfExists('categories');
    }
};
