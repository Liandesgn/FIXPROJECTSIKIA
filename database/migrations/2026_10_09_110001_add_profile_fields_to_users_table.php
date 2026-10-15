<?php

use App\Enums\UserStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('status', 20)->default(UserStatusEnum::Active->value)->after('password');
            $table->foreignId('wilayah_kerja_id')
                ->nullable()
                ->after('status')
                ->constrained('wilayah_kerja')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wilayah_kerja_id');
            $table->dropColumn(['phone', 'status']);
        });
    }
};
