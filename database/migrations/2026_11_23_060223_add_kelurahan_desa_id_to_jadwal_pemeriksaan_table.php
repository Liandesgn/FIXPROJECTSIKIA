
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pemeriksaan', function (Blueprint $table) {
            $table->foreignId('kelurahan_desa_id')
                ->nullable()
                ->after('id_wilayah_kerja')
                ->constrained('kelurahan_desa')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pemeriksaan', function (Blueprint $table) {
            $table->dropForeign(['kelurahan_desa_id']);
            $table->dropColumn('kelurahan_desa_id');
        });
    }
};
