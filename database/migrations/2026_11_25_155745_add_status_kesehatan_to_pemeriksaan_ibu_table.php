
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pemeriksaan_ibu', 'status_kesehatan')) {
            Schema::table('pemeriksaan_ibu', function (Blueprint $table) {
                $table->string('status_kesehatan', 30)
                    ->default('normal');
            });
        }
    }

    public function down(): void
    {
        // Kolom tidak dihapus karena mungkin sudah ada
        // sebelum migration ini dijalankan.
    }
};
