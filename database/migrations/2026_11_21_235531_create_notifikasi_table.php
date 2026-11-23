
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Penerima notifikasi
            $table->morphs('notifiable');

            // Format penyimpanan notifikasi Laravel
            $table->string('type');
            $table->text('data');

            // Jadwal yang berkaitan dengan notifikasi
            $table->foreignId('id_jadwal')
                ->nullable()
                ->constrained('jadwal', 'id_jadwal')
                ->nullOnDelete();

            // Informasi notifikasi
            $table->string('jenis')->nullable();
            $table->string('judul')->nullable();
            $table->text('isi')->nullable();

            // Status baca
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
