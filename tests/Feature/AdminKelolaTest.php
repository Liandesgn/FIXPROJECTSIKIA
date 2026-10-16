<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKelolaTest extends TestCase
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

    private function admin(): User
    {
        return $this->userWithRole(RoleEnum::Admin);
    }

    public function test_admin_can_register_ibu_yang_hamil(): void
    {
        $this->actingAs($this->admin())->post('/admin/ibu', [
            'name' => 'Siti Aminah',
            'email' => 'siti.hamil@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101900001',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1990-01-01',
            'phone' => '081234567890',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(3)->toDateString(),
            'usia_kehamilan' => 24,
        ])->assertRedirect(route('admin.ibu.index'));

        $user = User::where('email', 'siti.hamil@sikia.test')->firstOrFail();
        $this->assertTrue($user->hasRole(RoleEnum::OrangTua->value));
        $this->assertSame('Siti Aminah', $user->name);
        $this->assertSame('3525010101900001', $user->nik);
        $this->assertSame(StatusKehamilanEnum::Hamil, $user->status_kehamilan);
        $this->assertSame(24, $user->usia_kehamilan);
        $this->assertNotNull($user->hpl);
    }

    public function test_register_ibu_does_not_require_nama_ibu(): void
    {
        $this->actingAs($this->admin())->post('/admin/ibu', [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101910002',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1991-02-02',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ])->assertRedirect(route('admin.ibu.index'));

        $user = User::where('email', 'dewi@sikia.test')->firstOrFail();
        $this->assertNull($user->nama_ibu);
        $this->assertNull($user->hpl);
        $this->assertNull($user->usia_kehamilan);
    }

    public function test_register_ibu_requires_hpl_when_hamil(): void
    {
        $this->actingAs($this->admin())->post('/admin/ibu', [
            'name' => 'Rina',
            'email' => 'rina@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101920003',
            'tempat_lahir' => 'Gresik',
            'tanggal_lahir' => '1992-03-03',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
        ])->assertSessionHasErrors(['hpl', 'usia_kehamilan']);
    }

    public function test_kelola_ibu_can_filter_by_search_wilayah_and_status(): void
    {
        $lain = WilayahKerja::create(['kabupaten' => 'Gresik', 'kecamatan' => 'Driyorejo', 'desa' => 'Banjaran', 'nama' => 'Desa Banjaran']);
        $ibu = $this->userWithRole(RoleEnum::OrangTua, ['name' => 'Ani Sumberame', 'wilayah_kerja_id' => $this->wilayah->id]);
        $other = $this->userWithRole(RoleEnum::OrangTua, ['name' => 'Budi Banjaran', 'wilayah_kerja_id' => $lain->id, 'status' => UserStatusEnum::Inactive->value]);

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/ibu?search=Ani')
            ->assertOk()->assertSee('Ani Sumberame')->assertDontSee('Budi Banjaran');

        $this->actingAs($admin)->get('/admin/ibu?wilayah_kerja_id='.$this->wilayah->id)
            ->assertOk()->assertSee('Ani Sumberame')->assertDontSee('Budi Banjaran');

        $this->actingAs($admin)->get('/admin/ibu?status='.UserStatusEnum::Inactive->value)
            ->assertOk()->assertSee('Budi Banjaran')->assertDontSee('Ani Sumberame');

        $this->assertNotNull($ibu->fresh());
        $this->assertNotNull($other->fresh());
    }

    public function test_admin_can_update_ibu(): void
    {
        $ibu = $this->userWithRole(RoleEnum::OrangTua, ['wilayah_kerja_id' => $this->wilayah->id]);

        $this->actingAs($this->admin())->put("/admin/ibu/{$ibu->id}", [
            'name' => 'Nama Diubah',
            'email' => $ibu->email,
            'nik' => '3525010101930004',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1993-04-04',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ])->assertRedirect(route('admin.ibu.index'));

        $this->assertSame('Nama Diubah', $ibu->fresh()->name);
        $this->assertSame('3525010101930004', $ibu->fresh()->nik);
    }

    public function test_admin_can_delete_bidan(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan, ['wilayah_kerja_id' => $this->wilayah->id]);

        $this->actingAs($this->admin())->delete("/admin/bidan/{$bidan->id}")
            ->assertRedirect(route('admin.bidan.index'));

        $this->assertDatabaseMissing('users', ['id' => $bidan->id]);
    }

    public function test_kelola_bidan_rejects_non_bidan_user(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/admin/bidan/{$admin->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_ibu_is_connected_to_bidan_and_kader_in_same_wilayah(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan, ['wilayah_kerja_id' => $this->wilayah->id]);
        $kader = $this->userWithRole(RoleEnum::Kader, ['wilayah_kerja_id' => $this->wilayah->id]);
        $ibu = $this->userWithRole(RoleEnum::OrangTua, ['wilayah_kerja_id' => $this->wilayah->id]);

        $this->assertTrue($ibu->bidanWilayah->is($bidan));
        $this->assertTrue($ibu->kaderWilayah->is($kader));
    }

    public function test_ibu_sees_mother_data_in_profile(): void
    {
        $ibu = $this->userWithRole(RoleEnum::OrangTua, [
            'wilayah_kerja_id' => $this->wilayah->id,
            'nik' => '3525010101900009',
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(2)->toDateString(),
            'usia_kehamilan' => 20,
        ]);

        $this->actingAs($ibu)->get('/profile')
            ->assertOk()
            ->assertSee('Data Ibu')
            ->assertSee('3525010101900009');
    }

    public function test_ibu_dashboard_shows_assigned_staff(): void
    {
        $bidan = $this->userWithRole(RoleEnum::Bidan, ['wilayah_kerja_id' => $this->wilayah->id]);
        $kader = $this->userWithRole(RoleEnum::Kader, ['wilayah_kerja_id' => $this->wilayah->id]);
        $ibu = $this->userWithRole(RoleEnum::OrangTua, ['wilayah_kerja_id' => $this->wilayah->id]);

        $this->actingAs($ibu)->get('/orang-tua')
            ->assertOk()
            ->assertSee($bidan->name)
            ->assertSee($kader->name);
    }
}
