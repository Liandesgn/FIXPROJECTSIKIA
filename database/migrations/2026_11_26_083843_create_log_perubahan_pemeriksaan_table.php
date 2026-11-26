
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_perubahan_pemeriksaan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pemeriksaan_ibu_id')
                ->constrained('pemeriksaan_ibu', 'id')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('aksi', 30);
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();

            $table->timestamps();

            $table->index(['pemeriksaan_ibu_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_perubahan_pemeriksaan');
    }
};
