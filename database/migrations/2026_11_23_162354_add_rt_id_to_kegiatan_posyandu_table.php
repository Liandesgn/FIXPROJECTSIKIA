
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan_posyandu', function (Blueprint $table) {
            $table->foreignId('rt_id')
                ->nullable()
                ->after('rw_id')
                ->constrained('rt')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan_posyandu', function (Blueprint $table) {
            $table->dropForeign(['rt_id']);
            $table->dropColumn('rt_id');
        });
    }
};
