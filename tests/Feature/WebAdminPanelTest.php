<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin-panel@sikia.test',
            'password' => 'password',
        ]);
        $admin->assignRole(RoleEnum::Admin->value);

        return $admin;
    }

    public function test_login_page_is_accessible_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke SI-KIA');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_dashboard(): void
    {
        $this->admin();

        $this->post('/login', [
            'email' => 'admin-panel@sikia.test',
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();

        $this->get('/admin')->assertOk()->assertSee('Ringkasan Sistem');
    }

    public function test_non_admin_is_redirected_to_own_dashboard(): void
    {
        $bidan = User::factory()->create([
            'email' => 'bidan-panel@sikia.test',
            'password' => 'password',
        ]);
        $bidan->assignRole(RoleEnum::Bidan->value);

        $this->post('/login', [
            'email' => 'bidan-panel@sikia.test',
            'password' => 'password',
        ])->assertRedirect('/bidan');

        $this->assertAuthenticated();
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $bidan = User::factory()->create();
        $bidan->assignRole(RoleEnum::Bidan->value);

        $this->actingAs($bidan)->get('/admin')->assertStatus(403);
    }

    public function test_admin_can_view_users_index(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Manajemen User');
    }

    public function test_admin_can_create_user_from_panel(): void
    {
        $wilayah = WilayahKerja::create(['nama' => 'Puskesmas Panel']);

        $this->actingAs($this->admin())
            ->post('/admin/users', [
                'name' => 'Kader Baru',
                'email' => 'kader-baru@sikia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '081122334455',
                'role' => RoleEnum::Kader->value,
                'status' => UserStatusEnum::Active->value,
                'wilayah_kerja_id' => $wilayah->id,
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['email' => 'kader-baru@sikia.test']);
    }

    public function test_admin_can_deactivate_user_from_panel(): void
    {
        $user = User::factory()->create(['status' => UserStatusEnum::Active->value]);
        $user->assignRole(RoleEnum::Kader->value);

        $this->actingAs($this->admin())
            ->patch("/admin/users/{$user->id}/deactivate")
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatusEnum::Inactive->value,
        ]);
    }
}
