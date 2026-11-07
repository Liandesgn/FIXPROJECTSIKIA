<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pemeriksaan_posyandu')) {
            Schema::create('pemeriksaan_posyandu', function (Blueprint $table) {
                $table->id();

                $table->foreignId('anak_id')
                    ->constrained('anak')
                    ->cascadeOnDelete();

                $table->foreignId('kegiatan_posyandu_id')
                    ->constrained('kegiatan_posyandu')
                    ->restrictOnDelete();

                $table->decimal('tinggi_badan', 5, 2)->nullable();
                $table->decimal('berat_badan', 5, 2)->nullable();

                $table->enum('status_kesehatan', [
                    'normal',
                    'perlu_perhatian'
                ])->default('normal');

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_posyandu');
    }
};