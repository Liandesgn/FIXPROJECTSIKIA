<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidan', function (Blueprint $table) {
            $table->unique(
                'wilayah_kerja_id',
                'bidan_wilayah_kerja_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bidan', function (Blueprint $table) {
            $table->dropUnique('bidan_wilayah_kerja_unique');
        });
    }
};
