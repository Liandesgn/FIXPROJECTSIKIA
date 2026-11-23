
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_posyandu', function (Blueprint $table) {
            $table->id('id_jadwal_posyandu');

            $table->foreignId('id_jadwal')
                ->constrained('jadwal', 'id_jadwal')
                ->cascadeOnDelete();

            $table->foreignId('id_kegiatan')
                ->constrained('kegiatan_posyandu')
                ->restrictOnDelete();

            $table->foreignId('id_bidan')
                ->constrained('bidan')
                ->restrictOnDelete();

            $table->foreignId('id_kader')
                ->constrained('kader')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_posyandu');
    }
};
