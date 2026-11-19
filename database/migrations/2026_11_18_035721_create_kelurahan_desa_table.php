
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelurahan_desa', function (Blueprint $table) {
            $table->id();

            $table->foreignId('puskesmas_id')
                ->constrained('puskesmas')
                ->restrictOnDelete();

            $table->foreignId('kecamatan_id')
                ->constrained('kecamatan')
                ->restrictOnDelete();

            $table->string('nama_kelurahan_desa', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahan_desa');
    }
};
