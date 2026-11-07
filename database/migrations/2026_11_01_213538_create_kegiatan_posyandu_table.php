<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kegiatan_posyandu')) {
            Schema::create('kegiatan_posyandu', function (Blueprint $table) {
                $table->id();

                $table->foreignId('posyandu_id')
                    ->constrained('posyandu')
                    ->cascadeOnDelete();

                $table->date('tanggal');
                $table->time('waktu')->nullable();
                $table->string('lokasi', 255)->nullable();

                $table->timestamps();
                $table->index('tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_posyandu');
    }
};