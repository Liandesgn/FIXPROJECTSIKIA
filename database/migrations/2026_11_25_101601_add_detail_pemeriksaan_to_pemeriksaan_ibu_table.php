
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaan_ibu', function (Blueprint $table) {
            $table->foreignId('id_bidan')
                ->nullable()
                ->after('ibu_id')
                ->constrained('bidan')
                ->nullOnDelete();

            $table->unsignedSmallInteger('usia_kehamilan')
                ->nullable()
                ->after('tgl_pemeriksaan');

            $table->decimal('berat_badan', 5, 2)
                ->nullable()
                ->after('usia_kehamilan');

            $table->decimal('tinggi_badan', 5, 2)
                ->nullable()
                ->after('berat_badan');

            $table->decimal('lila', 5, 2)
                ->nullable()
                ->after('tinggi_badan');

            $table->decimal('hb', 4, 2)
                ->nullable()
                ->after('lila');

            $table->unsignedSmallInteger('tekanan_sistolik')
                ->nullable()
                ->after('hb');

            $table->unsignedSmallInteger('tekanan_diastolik')
                ->nullable()
                ->after('tekanan_sistolik');

            $table->string('proteinuria', 10)
                ->nullable()
                ->after('tekanan_diastolik');

            $table->boolean('gejala_berat')
                ->default(false)
                ->after('proteinuria');

            $table->text('keluhan')
                ->nullable()
                ->after('gejala_berat');

            $table->text('kondisi_kehamilan')
                ->nullable()
                ->after('keluhan');

            $table->unsignedSmallInteger('skor_risiko')
                ->nullable()
                ->after('kondisi_kehamilan');

            $table->enum('tingkat_risiko', [
                'rendah',
                'sedang',
                'tinggi',
            ])->nullable()->after('skor_risiko');

            $table->text('faktor_risiko')
                ->nullable()
                ->after('tingkat_risiko');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_ibu', function (Blueprint $table) {
            $table->dropForeign(['id_bidan']);

            $table->dropColumn([
                'id_bidan',
                'usia_kehamilan',
                'berat_badan',
                'tinggi_badan',
                'lila',
                'hb',
                'tekanan_sistolik',
                'tekanan_diastolik',
                'proteinuria',
                'gejala_berat',
                'keluhan',
                'kondisi_kehamilan',
                'skor_risiko',
                'tingkat_risiko',
                'faktor_risiko',
            ]);
        });
    }
};
