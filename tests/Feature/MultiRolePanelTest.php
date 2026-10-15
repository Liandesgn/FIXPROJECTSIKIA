<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MultiRolePanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(RoleEnum $role): User
    {
        $user = User::factory()->create(['password' => 'password']);
        $user->assignRole($role->value);

        return $user;
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function dashboardProvider(): array
    {
        return [
            'admin' => [RoleEnum::Admin->value, '/admin', 'Ringkasan Sistem'],
            'bidan' => [RoleEnum::Bidan->value, '/bidan', 'Dashboard Bidan'],
            'kader' => [RoleEnum::Kader->value, '/kader', 'Dashboard Kader'],
            'orang_tua' => [RoleEnum::OrangTua->value, '/orang-tua', 'Layanan untuk Anda'],
        ];
    }

    #[DataProvider('dashboardProvider')]
    public function test_each_role_is_redirected_to_its_dashboard(string $roleValue, string $expectedPath, string $marker): void
    {
        $user = $this->userWithRole(RoleEnum::from($roleValue));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect($expectedPath);

        $this->get($expectedPath)->assertOk()->assertSee($marker);
    }

    public function test_guest_cannot_access_role_dashboards(): void
    {
        foreach (['/admin', '/bidan', '/kader', '/orang-tua'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_role_dashboards_are_isolated(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan);

        $this->actingAs($bidan)->get('/admin')->assertStatus(403);
        $this->actingAs($bidan)->get('/kader')->assertStatus(403);
        $this->actingAs($bidan)->get('/orang-tua')->assertStatus(403);

        $admin = $this->userWithRole(RoleEnum::Admin);
        $this->actingAs($admin)->get('/bidan')->assertStatus(403);

        $orangTua = $this->userWithRole(RoleEnum::OrangTua);
        $this->actingAs($orangTua)->get('/kader')->assertStatus(403);
    }

    public function test_every_role_can_open_shared_profile(): void
    {
        foreach ([RoleEnum::Admin, RoleEnum::Bidan, RoleEnum::Kader, RoleEnum::OrangTua] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get('/profile')
                ->assertOk()
                ->assertSee('Informasi Profil');
        }
    }

    public function test_authenticated_user_can_open_placeholder_module(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::Bidan))
            ->get('/modul/data-ibu')
            ->assertOk()
            ->assertSee('Data Ibu');
    }

    public function test_unknown_module_returns_not_found(): void
    {
        $this->actingAs($this->userWithRole(RoleEnum::Bidan))
            ->get('/modul/tidak-ada')
            ->assertNotFound();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->userWithRole(RoleEnum::Kader);
        $user->update(['status' => UserStatusEnum::Inactive]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
