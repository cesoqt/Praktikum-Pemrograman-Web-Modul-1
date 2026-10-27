<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->char('kode_produk', 10)->unique();
            $table->string('nama_produk', 100);
            $table->string('sku', 30)->unique();
            $table->unsignedSmallInteger('stok');
            $table->foreignId('kategori_produk_id')
                ->constrained('kategori_produk')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE produk ADD CONSTRAINT chk_produk_stok CHECK (stok BETWEEN 0 AND 500)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
