<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->renameColumn('nama', 'nama_wilayah');
        });
    }

    public function down(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->renameColumn('nama_wilayah', 'nama');
        });
    }
};