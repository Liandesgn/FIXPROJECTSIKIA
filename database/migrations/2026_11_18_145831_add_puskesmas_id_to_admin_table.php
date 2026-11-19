
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->foreignId('puskesmas_id')
                ->nullable()
                ->unique()
                ->constrained('puskesmas')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign(['puskesmas_id']);
            $table->dropUnique(['puskesmas_id']);
            $table->dropColumn('puskesmas_id');
        });
    }
};
