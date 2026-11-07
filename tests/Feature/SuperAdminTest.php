<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        return $this->makeUser(RoleEnum::SuperAdmin);
    }

    public function test_super_admin_can_open_management_pages(): void
    {
        $superAdmin = $this->superAdmin();
        $this->makeWilayah();

        $this->actingAs($superAdmin)->get('/superadmin')
            ->assertOk()
            ->assertSee('Dashboard Super Admin');

        $this->actingAs($superAdmin)->get('/superadmin/admins')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/admins/create')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/wilayah-kerja')
            ->assertOk()
            ->assertSee('href="'.route('superadmin.wilayah-kerja.index').'"', false)
            ->assertDontSee('Segera hadir');
        $this->actingAs($superAdmin)->get('/superadmin/roles')->assertOk();
        $this->actingAs($superAdmin)->get('/superadmin/permissions')->assertOk();
    }

    public function test_super_admin_can_create_admin_with_managed_wilayah(): void
    {
        $wilayah = $this->makeWilayah();

        $this->actingAs($this->superAdmin())
            ->post('/superadmin/admins', [
                'name' => 'Admin Wilayah A',
                'email' => 'admin.a@sikia.test',
                'password' => 'password123',
                'phone' => '081234567890',
                'id_wilayah' => $wilayah->id,
            ])
            ->assertRedirect(route('superadmin.admins.index'));

        $user = User::where('email', 'admin.a@sikia.test')->firstOrFail();

        $this->assertTrue($user->hasRole(RoleEnum::Admin->value));
        $this->assertSame($wilayah->id, $user->admin?->id_wilayah);
    }

    public function test_super_admin_cannot_assign_one_wilayah_to_two_admins(): void
    {
        $wilayah = $this->makeWilayah();
        $this->makeAdmin($wilayah);

        $this->actingAs($this->superAdmin())
            ->post('/superadmin/admins', [
                'name' => 'Admin Kedua',
                'email' => 'admin.kedua@sikia.test',
                'password' => 'password123',
                'id_wilayah' => $wilayah->id,
            ])
            ->assertSessionHasErrors('id_wilayah');

        $this->assertDatabaseMissing('users', ['email' => 'admin.kedua@sikia.test']);
    }

    public function test_super_admin_can_update_and_delete_admin(): void
    {
        $wilayah = $this->makeWilayah();
        $newWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);
        $admin = $this->makeAdmin($wilayah, ['email' => 'admin.edit@sikia.test']);

        $this->actingAs($this->superAdmin())
            ->put("/superadmin/admins/{$admin->admin->id}", [
                'name' => 'Admin Diubah',
                'email' => 'admin.diubah@sikia.test',
                'id_wilayah' => $newWilayah->id,
            ])
            ->assertRedirect(route('superadmin.admins.index'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Diubah',
            'email' => 'admin.diubah@sikia.test',
        ]);
        $this->assertDatabaseHas('admin', [
            'user_id' => $admin->id,
            'id_wilayah' => $newWilayah->id,
        ]);

        $this->actingAs($this->superAdmin())
            ->delete("/superadmin/admins/{$admin->admin->id}")
            ->assertRedirect(route('superadmin.admins.index'));

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('admin', ['user_id' => $admin->id]);
    }

    public function test_admin_cannot_access_super_admin_area(): void
    {
        $admin = $this->makeAdmin($this->makeWilayah());

        $this->actingAs($admin)->get('/superadmin')->assertStatus(403);
        $this->actingAs($admin)->get('/superadmin/admins')->assertStatus(403);
    }
}
