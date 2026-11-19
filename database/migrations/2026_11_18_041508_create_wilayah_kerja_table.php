
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->foreignId('kecamatan_id')
                ->nullable()
                ->constrained('kecamatan')
                ->restrictOnDelete();

            $table->foreignId('puskesmas_id')
                ->nullable()
                ->constrained('puskesmas')
                ->restrictOnDelete();

            $table->foreignId('kelurahan_desa_id')
                ->nullable()
                ->constrained('kelurahan_desa')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
            $table->dropForeign(['puskesmas_id']);
            $table->dropForeign(['kelurahan_desa_id']);

            $table->dropColumn([
                'kecamatan_id',
                'puskesmas_id',
                'kelurahan_desa_id',
            ]);
        });
    }
};
