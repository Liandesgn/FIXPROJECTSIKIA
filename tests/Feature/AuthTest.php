<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'bidan@sikia.test',
            'status' => UserStatusEnum::Active->value,
        ]);
        $user->assignRole(RoleEnum::Bidan->value);

        $this->postJson('/api/auth/login', [
            'email' => 'bidan@sikia.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email', 'bidan@sikia.test')
            ->assertJsonPath('data.user.role', RoleEnum::Bidan->value)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonStructure(['data' => ['user', 'token', 'dashboard']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'salah@sikia.test']);

        $this->postJson('/api/auth/login', [
            'email' => 'salah@sikia.test',
            'password' => 'password-yang-salah',
        ])->assertStatus(401);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'nonaktif@sikia.test',
            'status' => UserStatusEnum::Inactive->value,
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'nonaktif@sikia.test',
            'password' => 'password',
        ])->assertStatus(403);
    }

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_guest_cannot_access_me_endpoint(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $this->withToken($token->plainTextToken)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
