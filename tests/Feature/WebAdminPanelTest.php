<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class WebAdminPanelTest extends TestCase
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
        return $this->makeAdmin($this->wilayah, [
            'email' => 'admin-panel@sikia.test',
            'password' => 'password',
        ]);
    }

    public function test_login_page_is_accessible_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login')->assertSee('SI-KIA');
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

        $this->get('/admin')->assertOk()->assertSee('Dashboard Admin');
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
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah, [
            'email' => 'bidan-panel@sikia.test',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'email' => $bidan->email,
            'password' => 'password',
        ])->assertRedirect('/bidan');

        $this->assertAuthenticated();
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);

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
        $this->actingAs($this->admin())
            ->post('/admin/bidan', [
                'name' => 'Bidan Baru',
                'email' => 'bidan-baru@sikia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '081122334455',
                'wilayah_kerja_id' => $this->wilayah->id,
            ])
            ->assertRedirect('/admin/bidan');

        $user = User::where('email', 'bidan-baru@sikia.test')->firstOrFail();

        $this->assertSame($this->wilayah->id, $user->bidan?->wilayah_kerja_id);
    }

    public function test_admin_can_create_kader_from_panel(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/kader', [
                'name' => 'Kader Baru',
                'email' => 'kader-baru@sikia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'phone' => '081122334455',
                'wilayah_kerja_id' => $this->wilayah->id,
            ])
            ->assertRedirect('/admin/kader');

        $user = User::where('email', 'kader-baru@sikia.test')->firstOrFail();

        $this->assertSame($this->wilayah->id, $user->kader?->wilayah_kerja_id);
    }

    public function test_admin_cannot_create_bidan_in_another_wilayah(): void
    {
        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/bidan', [
                'name' => 'Bidan Luar',
                'email' => 'bidan-luar@sikia.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'wilayah_kerja_id' => $otherWilayah->id,
            ])
            ->assertSessionHasErrors('wilayah_kerja_id');

        $this->assertDatabaseMissing('users', ['email' => 'bidan-luar@sikia.test']);
    }

    public function test_admin_can_toggle_ibu_status_from_panel(): void
    {
        $ibu = $this->makeUser(RoleEnum::Ibu, $this->wilayah, [
            'status' => UserStatusEnum::Active->value,
        ]);

        $this->actingAs($this->admin())
            ->patch("/admin/ibu/{$ibu->id}/toggle-status")
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $ibu->id,
            'status' => UserStatusEnum::Inactive->value,
        ]);
    }
}
