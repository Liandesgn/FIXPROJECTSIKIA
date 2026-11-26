
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      
Schema::create('jadwal_vitamin', function (Blueprint $table) {
    $table->smallIncrements('id_jadwal_vitamin');

    $table->string('jenis_vitamin');
    $table->string('dosis');

    $table->unsignedInteger('usia_min_bulan');
    $table->unsignedInteger('usia_maks_bulan');

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_vitamin');
    }
};