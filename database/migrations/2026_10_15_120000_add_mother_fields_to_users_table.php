<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nik', 16)->nullable()->after('phone');
            $table->string('nama_ibu')->nullable()->after('nik');
            $table->string('tempat_lahir')->nullable()->after('nama_ibu');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('status_kehamilan', 20)->nullable()->after('tanggal_lahir');
            $table->date('hpl')->nullable()->after('status_kehamilan');
            $table->unsignedTinyInteger('usia_kehamilan')->nullable()->after('hpl');
        });
    }

    public function down(): void
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
};
