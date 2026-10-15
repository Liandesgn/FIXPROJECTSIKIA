<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KelolaOrangTuaTest extends TestCase
{
    use RefreshDatabase;

    private WilayahKerja $wilayah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->wilayah = WilayahKerja::create([
            'kabupaten' => 'Gresik',
            'kecamatan' => 'Wringinanom',
            'desa' => 'Sumberame',
            'nama' => 'Desa Sumberame, Kec. Wringinanom',
        ]);
    }

    private function userWithRole(RoleEnum $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role->value);

        return $user;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function staffRoleProvider(): array
    {
        return [
            'admin' => [RoleEnum::Admin->value],
            'bidan' => [RoleEnum::Bidan->value],
            'kader' => [RoleEnum::Kader->value],
        ];
    }

    #[DataProvider('staffRoleProvider')]
    public function test_staff_can_open_kelola_orang_tua(string $role): void
    {
        $user = $this->userWithRole(RoleEnum::from($role));

        $this->actingAs($user)->get('/kelola-orang-tua')->assertOk()->assertSee('Kelola Orang Tua');
        $this->actingAs($user)->get('/kelola-orang-tua/create')->assertOk()->assertSee('Registrasi Orang Tua');
    }

    public function test_orang_tua_cannot_open_kelola_orang_tua(): void
    {
        $orangTua = $this->userWithRole(RoleEnum::OrangTua);

        $this->actingAs($orangTua)->get('/kelola-orang-tua')->assertStatus(403);
    }

    public function test_staff_can_register_orang_tua_yang_hamil(): void
    {
        $admin = $this->userWithRole(RoleEnum::Admin);

        $this->actingAs($admin)->post('/kelola-orang-tua', [
            'name' => 'Akun Siti',
            'email' => 'siti.hamil@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101900001',
            'nama_ibu' => 'Siti Aminah',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1990-01-01',
            'phone' => '081234567890',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(3)->toDateString(),
            'usia_kehamilan' => 24,
        ])->assertRedirect(route('panel.kelola-orang-tua.index'));

        $user = User::where('email', 'siti.hamil@sikia.test')->firstOrFail();
        $this->assertTrue($user->hasRole(RoleEnum::OrangTua->value));
        $this->assertSame('3525010101900001', $user->nik);
        $this->assertSame(StatusKehamilanEnum::Hamil, $user->status_kehamilan);
        $this->assertSame(24, $user->usia_kehamilan);
        $this->assertNotNull($user->hpl);
    }

    public function test_register_requires_hpl_only_when_hamil(): void
    {
        $admin = $this->userWithRole(RoleEnum::Admin);

        $this->actingAs($admin)->post('/kelola-orang-tua', [
            'name' => 'Akun Dewi',
            'email' => 'dewi@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101910002',
            'nama_ibu' => 'Dewi Lestari',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1991-02-02',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ])->assertRedirect(route('panel.kelola-orang-tua.index'));

        $user = User::where('email', 'dewi@sikia.test')->firstOrFail();
        $this->assertSame(StatusKehamilanEnum::Melahirkan, $user->status_kehamilan);
        $this->assertNull($user->hpl);
        $this->assertNull($user->usia_kehamilan);
    }

    public function test_register_requires_hpl_when_hamil(): void
    {
        $admin = $this->userWithRole(RoleEnum::Admin);

        $this->actingAs($admin)->post('/kelola-orang-tua', [
            'name' => 'Akun Rina',
            'email' => 'rina@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101920003',
            'nama_ibu' => 'Rina',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1992-03-03',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
        ])->assertSessionHasErrors(['hpl', 'usia_kehamilan']);
    }

    public function test_orang_tua_is_connected_to_bidan_and_kader_in_same_wilayah(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan, ['wilayah_kerja_id' => $this->wilayah->id]);
        $kader = $this->userWithRole(RoleEnum::Kader, ['wilayah_kerja_id' => $this->wilayah->id]);
        $orangTua = $this->userWithRole(RoleEnum::OrangTua, ['wilayah_kerja_id' => $this->wilayah->id]);

        $this->assertTrue($orangTua->bidanWilayah->is($bidan));
        $this->assertTrue($orangTua->kaderWilayah->is($kader));
    }

    public function test_orang_tua_sees_mother_data_in_profile(): void
    {
        $orangTua = $this->userWithRole(RoleEnum::OrangTua, [
            'wilayah_kerja_id' => $this->wilayah->id,
            'nik' => '3525010101900009',
            'nama_ibu' => 'Ibu Profil',
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(2)->toDateString(),
            'usia_kehamilan' => 20,
        ]);

        $this->actingAs($orangTua)->get('/profile')
            ->assertOk()
            ->assertSee('Data Ibu')
            ->assertSee('Ibu Profil')
            ->assertSee('3525010101900009');
    }

    public function test_orang_tua_dashboard_shows_assigned_staff(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan, ['wilayah_kerja_id' => $this->wilayah->id]);
        $kader = $this->userWithRole(RoleEnum::Kader, ['wilayah_kerja_id' => $this->wilayah->id]);
        $orangTua = $this->userWithRole(RoleEnum::OrangTua, ['wilayah_kerja_id' => $this->wilayah->id]);

        $this->actingAs($orangTua)->get('/orang-tua')
            ->assertOk()
            ->assertSee($bidan->name)
            ->assertSee($kader->name);
    }
}
