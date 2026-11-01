<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('ibu', 'tanggal_pemeriksaan_awal')) {
            Schema::table('ibu', function (Blueprint $table) {
                $table->date('tanggal_pemeriksaan_awal')->nullable()->after('usia_kehamilan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ibu', 'tanggal_pemeriksaan_awal')) {
            Schema::table('ibu', function (Blueprint $table) {
                $table->dropColumn('tanggal_pemeriksaan_awal');
            });
        }
    }
};