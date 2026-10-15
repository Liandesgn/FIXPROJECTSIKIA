<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole(RoleEnum::Admin->value);

        return $admin;
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(3)->create();
        Sanctum::actingAs($this->admin());

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $bidan = User::factory()->create();
        $bidan->assignRole(RoleEnum::Bidan->value);

        Sanctum::actingAs($this->admin());

        $this->getJson('/api/users?role=bidan')
            ->assertOk()
            ->assertJsonPath('data.0.email', $bidan->email);
    }

    public function test_admin_can_create_orang_tua(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/users', [
            'name' => 'Ibu Siti',
            'email' => 'siti@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'role' => RoleEnum::OrangTua->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'siti@sikia.test')
            ->assertJsonPath('data.role', RoleEnum::OrangTua->value);

        $this->assertDatabaseHas('users', ['email' => 'siti@sikia.test']);
    }

    public function test_bidan_requires_wilayah_kerja(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/users', [
            'name' => 'Bidan Ani',
            'email' => 'ani@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => RoleEnum::Bidan->value,
        ])->assertStatus(422)->assertJsonValidationErrors('wilayah_kerja_id');
    }

    public function test_admin_can_update_user(): void
    {
        $wilayah = WilayahKerja::create(['nama' => 'Puskesmas A']);
        $user = User::factory()->create(['email' => 'lama@sikia.test']);
        $user->assignRole(RoleEnum::Kader->value);

        Sanctum::actingAs($this->admin());

        $this->putJson("/api/users/{$user->id}", [
            'name' => 'Nama Baru',
            'email' => 'baru@sikia.test',
            'role' => RoleEnum::Bidan->value,
            'wilayah_kerja_id' => $wilayah->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.role', RoleEnum::Bidan->value);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'baru@sikia.test']);
    }

    public function test_admin_can_activate_and_deactivate_user(): void
    {
        $user = User::factory()->create(['status' => UserStatusEnum::Inactive->value]);
        $user->assignRole(RoleEnum::Kader->value);
        Sanctum::actingAs($this->admin());

        $this->patchJson("/api/users/{$user->id}/activate")
            ->assertOk()
            ->assertJsonPath('data.status', UserStatusEnum::Active->value);

        $this->patchJson("/api/users/{$user->id}/deactivate")
            ->assertOk()
            ->assertJsonPath('data.status', UserStatusEnum::Inactive->value);
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole(RoleEnum::Kader->value);
        Sanctum::actingAs($this->admin());

        $this->deleteJson("/api/users/{$user->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/users/{$admin->id}")->assertStatus(403);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $bidan = User::factory()->create();
        $bidan->assignRole(RoleEnum::Bidan->value);
        Sanctum::actingAs($bidan);

        $this->getJson('/api/users')->assertStatus(403);
    }

    public function test_guest_cannot_manage_users(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }
}
