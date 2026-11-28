
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus hubungan Wilayah Kerja dengan satu RT.
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->dropForeign(['rt_id']);
            $table->dropColumn('rt_id');
        });

        // Setiap Wilayah Kerja wajib memiliki RW
        // dan satu RW hanya boleh memiliki satu Wilayah Kerja.
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->unsignedBigInteger('rw_id')->nullable(false)->change();

            $table->unique('rw_id', 'wilayah_kerja_rw_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wilayah_kerja', function (Blueprint $table) {
            $table->dropUnique('wilayah_kerja_rw_id_unique');

            $table->unsignedBigInteger('rw_id')->nullable()->change();

            // Mengembalikan kolom rt_id jika migration di-rollback.
            $table->foreignId('rt_id')
                ->nullable()
                ->after('rw_id')
                ->constrained('rt')
                ->restrictOnDelete();
        });
    }
};
