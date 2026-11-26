<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
            $table->decimal('lila', 5, 2)->nullable()->after('lingkar_kepala');
        });
    }

    public function down(): void
    {
        Schema::table('pemeriksaan_posyandu', function (Blueprint $table) {
            $table->dropColumn('lila');
        });
    }
};