<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelurahan_desa', function (Blueprint $table) {
            $table->enum('jenis', ['kelurahan', 'desa'])
                ->after('nama_kelurahan_desa');
        });
    }

    public function down(): void
    {
        Schema::table('kelurahan_desa', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};