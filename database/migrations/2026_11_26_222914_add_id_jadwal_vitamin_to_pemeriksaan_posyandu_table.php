<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pemeriksaan_posyandu', 'id_jadwal_vitamin')) {
            Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
                $table->unsignedSmallInteger('id_jadwal_vitamin')
                    ->nullable()
                    ->after('jadwal_vitamin');
            });
        }

        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
            $table->foreign('id_jadwal_vitamin')
                ->references('id_jadwal_vitamin')
                ->on('jadwal_vitamin')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
            $table->dropForeign(['id_jadwal_vitamin']);
            $table->dropColumn('id_jadwal_vitamin');
        });
    }
};