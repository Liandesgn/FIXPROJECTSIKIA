<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = $this->makeUser(RoleEnum::Ibu);
        Sanctum::actingAs($user);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_web_profile_is_read_only(): void
    {
        $user = $this->makeUser(RoleEnum::Bidan);

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Informasi akun yang terdaftar di SI-KIA')
            ->assertDontSee('name="name"', false);
    }

    public function test_api_profile_cannot_be_updated(): void
    {
        $user = $this->makeUser(RoleEnum::Ibu);
        Sanctum::actingAs($user);

        $this->putJson('/api/profile', [
            'name' => 'Nama Diubah',
            'phone' => '089876543210',
        ])->assertStatus(405);
    }
}
