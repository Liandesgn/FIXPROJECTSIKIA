<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('posyandu', 'kader_id')) {
            Schema::table('posyandu', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kader_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('posyandu', 'kader_id')) {
            Schema::table('posyandu', function (Blueprint $table) {
                $table->foreignId('kader_id')
                    ->nullable()
                    ->after('wilayah_kerja_id')
                    ->constrained('kader')
                    ->nullOnDelete();
            });
        }
    }
};