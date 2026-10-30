<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'nama_ibu',
                'tempat_lahir',
                'tanggal_lahir',
                'status_kehamilan',
                'hpl',
                'usia_kehamilan',
            ]);
        });
    }


    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('status_kehamilan', 20)->nullable();
            $table->date('hpl')->nullable();
            $table->unsignedTinyInteger('usia_kehamilan')->nullable();
        });
    }
};
