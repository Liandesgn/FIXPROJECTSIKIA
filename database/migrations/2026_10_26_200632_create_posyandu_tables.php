<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posyandu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_kerja_id')->constrained('wilayah_kerja')->restrictOnDelete();
            $table->string('nama_posyandu', 100)->unique();
            $table->string('alamat', 255);
            $table->timestamps();
        });

        Schema::create('kegiatan_posyandu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posyandu_id')->constrained('posyandu')->restrictOnDelete();
            $table->date('tanggal');
            $table->time('waktu')->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_posyandu');
        Schema::dropIfExists('posyandu');
    }
};