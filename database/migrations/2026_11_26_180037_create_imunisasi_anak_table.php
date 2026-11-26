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
      
Schema::create('imunisasi_anak', function (Blueprint $table) {
    $table->id('id_imunisasi');

    $table->foreignId('id_pemeriksaan')
        ->constrained('pemeriksaan_posyandu')
        ->cascadeOnDelete();

    $table->unsignedSmallInteger('id_jadwal_imunisasi');

    $table->foreign('id_jadwal_imunisasi')
        ->references('id_jadwal_imunisasi')
        ->on('jadwal_imunisasi')
        ->restrictOnDelete();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imunisasi_anak');
    }
};
