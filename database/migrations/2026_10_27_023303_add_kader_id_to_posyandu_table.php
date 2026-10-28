<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            $table->foreignId('kader_id')
                ->nullable()
                ->after('wilayah_kerja_id')
                ->constrained('kader')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posyandu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kader_id');
        });
    }
};