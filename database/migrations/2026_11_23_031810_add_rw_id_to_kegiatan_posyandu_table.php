<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_posyandu', function (Blueprint $table) {
            $table->foreignId('rw_id')
                ->nullable()
                ->after('id_wilayah')
                ->constrained('rw')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan_posyandu', function (Blueprint $table) {
            $table->dropForeign(['rw_id']);
            $table->dropColumn('rw_id');
        });
    }
};
