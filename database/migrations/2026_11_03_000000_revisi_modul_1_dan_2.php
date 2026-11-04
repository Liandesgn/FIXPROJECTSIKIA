<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('wilayah_kerja', 'desa')) {
            Schema::table('wilayah_kerja', fn (Blueprint $table) => $table->renameColumn('desa', 'kelurahan'));
        }

        if (Schema::hasColumn('ibu', 'tanggal_lahir')) {
            Schema::table('ibu', fn (Blueprint $table) => $table->renameColumn('tanggal_lahir', 'tgl_lahir'));
        }

        if (Schema::hasColumn('anak', 'tanggal_lahir')) {
            Schema::table('anak', fn (Blueprint $table) => $table->renameColumn('tanggal_lahir', 'tgl_lahir'));
        }

        if (! Schema::hasColumn('admin', 'id_wilayah')) {
            Schema::table('admin', function (Blueprint $table) {
                $table->foreignId('id_wilayah')
                    ->nullable()
                    ->unique()
                    ->after('user_id')
                    ->constrained('wilayah_kerja')
                    ->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('kegiatan_posyandu', 'id_wilayah')) {
            Schema::table('kegiatan_posyandu', function (Blueprint $table) {
                $table->foreignId('id_wilayah')
                    ->nullable()
                    ->after('id')
                    ->constrained('wilayah_kerja')
                    ->restrictOnDelete();
            });
        }

        if (! Schema::hasColumn('kegiatan_posyandu', 'jenis_kegiatan')) {
            Schema::table('kegiatan_posyandu', function (Blueprint $table) {
                $table->string('jenis_kegiatan', 100)->nullable()->after('lokasi');
            });
        }

        if (Schema::hasColumn('kegiatan_posyandu', 'posyandu_id') && Schema::hasTable('posyandu')) {
            DB::table('kegiatan_posyandu')
                ->whereNotNull('posyandu_id')
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        $wilayahId = DB::table('posyandu')
                            ->where('id', $row->posyandu_id)
                            ->value('wilayah_kerja_id');

                        DB::table('kegiatan_posyandu')
                            ->where('id', $row->id)
                            ->update(['id_wilayah' => $wilayahId]);
                    }
                });

            Schema::table('kegiatan_posyandu', function (Blueprint $table) {
                $table->dropConstrainedForeignId('posyandu_id');
            });
        }

        Schema::dropIfExists('posyandu');
    }

    public function down(): void
    {
        if (! Schema::hasTable('posyandu')) {
            Schema::create('posyandu', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wilayah_kerja_id')->constrained('wilayah_kerja')->restrictOnDelete();
                $table->string('nama_posyandu', 100)->unique();
                $table->string('alamat', 255);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('kegiatan_posyandu', 'posyandu_id')) {
            Schema::table('kegiatan_posyandu', function (Blueprint $table) {
                $table->foreignId('posyandu_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('posyandu')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('kegiatan_posyandu', 'jenis_kegiatan')) {
            Schema::table('kegiatan_posyandu', fn (Blueprint $table) => $table->dropColumn('jenis_kegiatan'));
        }

        if (Schema::hasColumn('kegiatan_posyandu', 'id_wilayah')) {
            Schema::table('kegiatan_posyandu', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_wilayah'));
        }

        if (Schema::hasColumn('admin', 'id_wilayah')) {
            Schema::table('admin', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_wilayah'));
        }

        if (Schema::hasColumn('anak', 'tgl_lahir')) {
            Schema::table('anak', fn (Blueprint $table) => $table->renameColumn('tgl_lahir', 'tanggal_lahir'));
        }

        if (Schema::hasColumn('ibu', 'tgl_lahir')) {
            Schema::table('ibu', fn (Blueprint $table) => $table->renameColumn('tgl_lahir', 'tanggal_lahir'));
        }

        if (Schema::hasColumn('wilayah_kerja', 'kelurahan')) {
            Schema::table('wilayah_kerja', fn (Blueprint $table) => $table->renameColumn('kelurahan', 'desa'));
        }
    }
};