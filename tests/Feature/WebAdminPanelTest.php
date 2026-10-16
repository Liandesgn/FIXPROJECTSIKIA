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

    public function test_login_failure_shows_indonesian_message(): void
    {
        $this->admin();

        $this->post('/login', [
            'email' => 'admin-panel@sikia.test',
            'password' => 'password-salah',
        ])->assertSessionHasErrors(['email' => 'Login gagal, Email atau password salah.']);

        $this->assertGuest();
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
        $this->actingAs($bidan)->get('/admin/bidan')->assertStatus(403);
        $this->actingAs($bidan)->get('/admin/kader')->assertStatus(403);
        $this->actingAs($bidan)->get('/admin/ibu')->assertStatus(403);
    }

    public function test_admin_can_view_kelola_pages(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/bidan')->assertOk()->assertSee('Kelola Bidan');
        $this->actingAs($admin)->get('/admin/kader')->assertOk()->assertSee('Kelola Kader');
        $this->actingAs($admin)->get('/admin/ibu')->assertOk()->assertSee('Kelola Ibu');
    }

    public function test_admin_can_create_bidan_from_panel(): void
    {
        $wilayah = WilayahKerja::create(['nama' => 'Puskesmas Panel']);

        $this->actingAs($this->admin())
            ->post('/admin/bidan', [
                'name' => 'Bidan Baru',
                'email' => 'bidan-baru@sikia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '081122334455',
                'wilayah_kerja_id' => $wilayah->id,
            ])
            ->assertRedirect('/admin/bidan');

        $this->assertDatabaseHas('users', ['email' => 'bidan-baru@sikia.test']);
    }

    public function test_admin_can_create_kader_from_panel(): void
    {
        $wilayah = WilayahKerja::create(['nama' => 'Puskesmas Panel']);

        $this->actingAs($this->admin())
            ->post('/admin/kader', [
                'name' => 'Kader Baru',
                'email' => 'kader-baru@sikia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '081122334455',
                'wilayah_kerja_id' => $wilayah->id,
            ])
            ->assertRedirect('/admin/kader');

        $this->assertDatabaseHas('users', ['email' => 'kader-baru@sikia.test']);
    }

    public function test_admin_can_toggle_ibu_status_from_panel(): void
    {
        $ibu = User::factory()->create(['status' => UserStatusEnum::Active->value]);
        $ibu->assignRole(RoleEnum::OrangTua->value);

        $this->actingAs($this->admin())
            ->patch("/admin/ibu/{$ibu->id}/toggle-status")
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $ibu->id,
            'status' => UserStatusEnum::Inactive->value,
        ]);
    }
}
