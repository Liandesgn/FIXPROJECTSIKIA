
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rt', function (Blueprint $table) {
            $table->unique(
                ['rw_id', 'nomor_rt'],
                'rt_rw_nomor_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('rt', function (Blueprint $table) {
            $table->dropUnique('rt_rw_nomor_unique');
        });
    }
};
