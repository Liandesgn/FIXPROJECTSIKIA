
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ibu', function (Blueprint $table) {
            $table->foreignId('rt_id')
                ->nullable()
                ->after('wilayah_kerja_id')
                ->constrained('rt')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ibu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rt_id');
        });
    }
};
