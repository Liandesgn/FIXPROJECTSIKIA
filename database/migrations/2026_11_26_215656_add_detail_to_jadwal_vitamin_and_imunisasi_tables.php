
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_vitamin', function (Blueprint $table) {
            $table->string('frekuensi')->nullable()->after('dosis');
        });

        Schema::table('jadwal_imunisasi', function (Blueprint $table) {
            $table->unsignedInteger('usia_maks_bulan')
                ->nullable()
                ->after('usia_min_bulan');

            $table->string('keterangan')
                ->nullable()
                ->after('wajib_nasional');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_imunisasi', function (Blueprint $table) {
            $table->dropColumn(['usia_maks_bulan', 'keterangan']);
        });

        Schema::table('jadwal_vitamin', function (Blueprint $table) {
            $table->dropColumn('frekuensi');
        });
    }
};
