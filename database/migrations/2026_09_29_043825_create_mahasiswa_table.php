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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->char('nim', 10)->unique();
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->unsignedTinyInteger('usia');

            $table->foreignId('program_studi_id')
                ->constrained('program_studi')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};