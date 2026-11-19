
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn('admin', 'id_wilayah') &&
            !Schema::hasColumn('admin', 'wilayah_kerja_id')
        ) {
            Schema::table('admin', function (Blueprint $table) {
                $table->dropForeign(['id_wilayah']);
                $table->dropUnique(['id_wilayah']);
                $table->renameColumn('id_wilayah', 'wilayah_kerja_id');
            });

            Schema::table('admin', function (Blueprint $table) {
                $table->unique('wilayah_kerja_id');
                $table->foreign('wilayah_kerja_id')
                    ->references('id')
                    ->on('wilayah_kerja')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn('admin', 'wilayah_kerja_id') &&
            !Schema::hasColumn('admin', 'id_wilayah')
        ) {
            Schema::table('admin', function (Blueprint $table) {
                $table->dropForeign(['wilayah_kerja_id']);
                $table->dropUnique(['wilayah_kerja_id']);
                $table->renameColumn('wilayah_kerja_id', 'id_wilayah');
            });

            Schema::table('admin', function (Blueprint $table) {
                $table->unique('id_wilayah');
                $table->foreign('id_wilayah')
                    ->references('id')
                    ->on('wilayah_kerja')
                    ->restrictOnDelete();
            });
        }
    }
};