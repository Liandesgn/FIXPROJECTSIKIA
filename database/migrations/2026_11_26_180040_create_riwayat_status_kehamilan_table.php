<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

    Schema::create('riwayat_status_kehamilan', function (Blueprint $table) {
        $table->id('id_riwayat');

        $table->foreignId('id_ibu')
            ->constrained('ibu')
            ->cascadeOnDelete();

        $table->enum('status_kehamilan', [
            'hamil',
            'melahirkan'
        ]);

        $table->date('tgl_status');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_kehamilan');
    }
};
