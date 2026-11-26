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
        
Schema::create('jadwal_imunisasi', function (Blueprint $table) {
    $table->smallIncrements('id_jadwal_imunisasi');

    $table->string('jenis_vaksin');
    $table->string('dosis');

    $table->unsignedInteger('usia_min_bulan');

    $table->boolean('wajib_nasional')->default(true);

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_imunisasi');
    }
};
