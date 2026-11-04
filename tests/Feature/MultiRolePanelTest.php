<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class MultiRolePanelTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function userWithRole(RoleEnum $role): User
    {
        return $role === RoleEnum::Admin
            ? $this->makeAdmin()
            : $this->makeUser($role);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function dashboardProvider(): array
    {
        return [
            'super_admin' => [RoleEnum::SuperAdmin->value, '/superadmin', 'Dashboard Super Admin'],
            'admin' => [RoleEnum::Admin->value, '/admin', 'Dashboard Admin'],
            'bidan' => [RoleEnum::Bidan->value, '/bidan', 'Dashboard Bidan'],
            'kader' => [RoleEnum::Kader->value, '/kader', 'Dashboard Kader'],
            'ibu' => [RoleEnum::Ibu->value, '/ibu', 'Dashboard Ibu'],
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
        foreach (['/superadmin', '/admin', '/bidan', '/kader', '/ibu'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_role_dashboards_are_isolated(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan);

        $this->actingAs($bidan)->get('/admin')->assertStatus(403);
        $this->actingAs($bidan)->get('/kader')->assertStatus(403);
        $this->actingAs($bidan)->get('/ibu')->assertStatus(403);
        $this->actingAs($bidan)->get('/superadmin')->assertStatus(403);

        $admin = $this->userWithRole(RoleEnum::Admin);
        $this->actingAs($admin)->get('/bidan')->assertStatus(403);
        $this->actingAs($admin)->get('/superadmin')->assertStatus(403);

        $ibu = $this->userWithRole(RoleEnum::Ibu);
        $this->actingAs($ibu)->get('/kader')->assertStatus(403);

        $superAdmin = $this->userWithRole(RoleEnum::SuperAdmin);
        $this->actingAs($superAdmin)->get('/admin')->assertStatus(403);
    }

    public function test_every_role_can_open_shared_profile(): void
    {
        foreach ([RoleEnum::SuperAdmin, RoleEnum::Admin, RoleEnum::Bidan, RoleEnum::Kader, RoleEnum::Ibu] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get('/profile')
                ->assertOk()
                ->assertSee('Informasi akun yang terdaftar di SI-KIA');
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
        $user->update(['status' => UserStatusEnum::Inactive->value]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
