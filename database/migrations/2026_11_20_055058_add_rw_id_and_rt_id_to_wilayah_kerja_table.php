
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->foreignId('rw_id')
                ->nullable()
                ->after('kelurahan_desa_id')
                ->constrained('rw')
                ->restrictOnDelete();

            $table->foreignId('rt_id')
                ->nullable()
                ->after('rw_id')
                ->constrained('rt')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->dropForeign(['rt_id']);
            $table->dropForeign(['rw_id']);

            $table->dropColumn(['rt_id', 'rw_id']);
        });
    }
};
