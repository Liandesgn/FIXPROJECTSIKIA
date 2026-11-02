<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pemeriksaan_ibu')) {
            Schema::create('pemeriksaan_ibu', function (Blueprint $table) {
                $table->id();

                $table->foreignId('ibu_id')
                    ->constrained('ibu')
                    ->cascadeOnDelete();

                $table->date('tgl_pemeriksaan');

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
        Schema::dropIfExists('pemeriksaan_ibu');
    }
};