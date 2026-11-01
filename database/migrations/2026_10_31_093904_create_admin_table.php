<?php

use App\Enums\RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Akun admin yang sudah ada langsung dibuatkan barisnya di tabel admin
        $roleId = DB::table('roles')->where('name', RoleEnum::Admin->value)->value('id');

        if ($roleId) {
            $userIds = DB::table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_type', 'App\\Models\\User')
                ->pluck('model_id');

            foreach ($userIds as $id) {
                DB::table('admin')->updateOrInsert(
                    ['user_id' => $id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};