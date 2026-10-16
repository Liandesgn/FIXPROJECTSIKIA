<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::OrangTua->value);
        Sanctum::actingAs($user);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_update_own_profile(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::OrangTua->value);
        Sanctum::actingAs($user);

        $this->putJson('/api/profile', [
            'name' => 'Nama Diubah',
            'phone' => '089876543210',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nama Diubah')
            ->assertJsonPath('data.phone', '089876543210');
    }
}
