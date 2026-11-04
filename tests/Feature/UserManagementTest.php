<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    private WilayahKerja $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->wilayah = $this->makeWilayah();
    }

    private function admin(): User
    {
        return $this->makeAdmin($this->wilayah);
    }

    public function test_admin_can_list_only_users_from_managed_wilayah(): void
    {
        $same = $this->makeUser(RoleEnum::Kader, $this->wilayah);
        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);
        $other = $this->makeUser(RoleEnum::Kader, $otherWilayah);

        Sanctum::actingAs($this->admin());

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta'])
            ->assertJsonFragment(['email' => $same->email])
            ->assertJsonMissing(['email' => $other->email]);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);
        $this->makeUser(RoleEnum::Kader, $this->wilayah);

        Sanctum::actingAs($this->admin());

        $this->getJson('/api/users?role=bidan')
            ->assertOk()
            ->assertJsonPath('data.0.email', $bidan->email)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_ibu_in_managed_wilayah(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/users', [
            'name' => 'Ibu Siti',
            'email' => 'siti@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'role' => RoleEnum::Ibu->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'siti@sikia.test')
            ->assertJsonPath('data.role', RoleEnum::Ibu->value);

        $this->assertDatabaseHas('users', [
            'email' => 'siti@sikia.test',
            'wilayah_kerja_id' => $this->wilayah->id,
        ]);
    }

    public function test_admin_can_create_bidan_in_managed_wilayah(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/users', [
            'name' => 'Bidan Ani',
            'email' => 'ani@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => RoleEnum::Bidan->value,
            'wilayah_kerja_id' => $this->wilayah->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', RoleEnum::Bidan->value);

        $this->assertDatabaseHas('users', [
            'email' => 'ani@sikia.test',
            'wilayah_kerja_id' => $this->wilayah->id,
        ]);

        $user = User::where('email', 'ani@sikia.test')->firstOrFail();
        $this->assertSame($this->wilayah->id, $user->bidan?->wilayah_kerja_id);
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

    public function test_admin_forces_managed_wilayah_when_creating_petugas(): void
    {
        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);

        Sanctum::actingAs($this->admin());

        $this->postJson('/api/users', [
            'name' => 'Bidan Lain',
            'email' => 'bidan.lain@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => RoleEnum::Bidan->value,
            'wilayah_kerja_id' => $otherWilayah->id,
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'bidan.lain@sikia.test',
            'wilayah_kerja_id' => $this->wilayah->id,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $user = $this->makeUser(RoleEnum::Kader, $this->wilayah, ['email' => 'lama@sikia.test']);

        Sanctum::actingAs($this->admin());

        $this->putJson("/api/users/{$user->id}", [
            'name' => 'Nama Baru',
            'email' => 'baru@sikia.test',
            'role' => RoleEnum::Kader->value,
            'wilayah_kerja_id' => $this->wilayah->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.role', RoleEnum::Kader->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'baru@sikia.test',
            'wilayah_kerja_id' => $this->wilayah->id,
        ]);
    }

    public function test_admin_cannot_manage_user_from_another_wilayah(): void
    {
        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);
        $other = $this->makeUser(RoleEnum::Kader, $otherWilayah);

        Sanctum::actingAs($this->admin());

        $this->getJson("/api/users/{$other->id}")->assertStatus(403);
        $this->putJson("/api/users/{$other->id}", ['name' => 'Diubah'])->assertStatus(403);
        $this->deleteJson("/api/users/{$other->id}")->assertStatus(403);
    }

    public function test_admin_can_activate_and_deactivate_user(): void
    {
        $user = $this->makeUser(RoleEnum::Kader, $this->wilayah, [
            'status' => UserStatusEnum::Inactive->value,
        ]);
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
        $user = $this->makeUser(RoleEnum::Kader, $this->wilayah);
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
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);
        Sanctum::actingAs($bidan);

        $this->getJson('/api/users')->assertStatus(403);
    }

    public function test_guest_cannot_manage_users(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }
}
