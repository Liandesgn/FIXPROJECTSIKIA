
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {

            // Pelaksana pemeriksaan: Kader atau Bidan
            $table->foreignId('kader_id')
                ->nullable()
                ->constrained('kader')
                ->nullOnDelete();

            $table->foreignId('bidan_id')
                ->nullable()
                ->constrained('bidan')
                ->nullOnDelete();

            $table->date('tgl_pemeriksaan')->nullable();
            $table->string('jadwal_vitamin')->nullable();

            // Detail pengukuran anak
            $table->decimal('lingkar_kepala', 5, 2)->nullable();

            // Data perhitungan pertumbuhan TB/U
            $table->unsignedSmallInteger('umur_bulan')->nullable();
            $table->decimal('z_score_tbu', 6, 3)->nullable();
            $table->decimal('median_tbu', 6, 2)->nullable();
            $table->decimal('sd_minus_1_tbu', 6, 2)->nullable();
            $table->decimal('sd_plus_1_tbu', 6, 2)->nullable();

            // Status pertumbuhan dan tingkat risiko
            $table->enum('status_stunting', [
                'normal',
                'pendek',
                'sangat_pendek',
            ])->nullable();

            $table->enum('tingkat_risiko', [
                'rendah',
                'sedang',
                'tinggi',
            ])->nullable();

            // Catatan pemeriksaan
            $table->text('catatan')->nullable();

            // Pelacakan akun yang menginput dan mengubah data
            $table->foreignId('pencatat_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('pengubah_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        // Hapus status kesehatan lama karena digantikan
        // oleh status_stunting dan tingkat_risiko.
        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
            $table->dropColumn('status_kesehatan');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
            // Kembalikan kolom status kesehatan
            $table->enum('status_kesehatan', [
                'normal',
                'perlu_perhatian',
            ])->nullable();

            // Hapus foreign key
            $table->dropForeign(['kader_id']);
            $table->dropForeign(['bidan_id']);
            $table->dropForeign(['pencatat_user_id']);
            $table->dropForeign(['pengubah_user_id']);

            // Hapus kolom tambahan
            $table->dropColumn([
                'kader_id',
                'bidan_id',
                'tgl_pemeriksaan',
                'jadwal_vitamin',
                'lingkar_kepala',
                'umur_bulan',
                'z_score_tbu',
                'median_tbu',
                'sd_minus_1_tbu',
                'sd_plus_1_tbu',
                'status_stunting',
                'tingkat_risiko',
                'catatan',
                'pencatat_user_id',
                'pengubah_user_id',
            ]);
        });
    }
};
