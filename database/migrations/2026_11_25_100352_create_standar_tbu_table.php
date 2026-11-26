
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standar_tbu', function (Blueprint $table) {
            $table->id();

            $table->string('jenis_kelamin', 20);
            $table->unsignedSmallInteger('umur_bulan');

            $table->decimal('median', 6, 2);
            $table->decimal('sd_minus_1', 6, 2);
            $table->decimal('sd_plus_1', 6, 2);

            $table->timestamps();

            $table->unique(
                ['jenis_kelamin', 'umur_bulan'],
                'standar_tbu_gender_umur_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standar_tbu');
    }
};
