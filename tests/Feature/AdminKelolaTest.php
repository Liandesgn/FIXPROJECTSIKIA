<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Enums\StatusKehamilanEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class AdminKelolaTest extends TestCase
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
        return $this->makeAdmin($this->wilayah);
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
            'tgl_lahir' => '1990-01-01',
            'phone' => '081234567890',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(3)->toDateString(),
            'usia_kehamilan' => 24,
        ])->assertRedirect(route('admin.ibu.index'));

        $user = User::where('email', 'siti.hamil@sikia.test')->firstOrFail();

        $this->assertTrue($user->hasRole(RoleEnum::Ibu->value));
        $this->assertSame('Siti Aminah', $user->name);

        $ibu = $user->ibu;

        $this->assertNotNull($ibu);
        $this->assertSame('3525010101900001', $ibu->nik);
        $this->assertSame(StatusKehamilanEnum::Hamil, $ibu->status_kehamilan);
        $this->assertSame(24, $ibu->usia_kehamilan);
        $this->assertNotNull($ibu->hpl);
        $this->assertSame($this->wilayah->id, $ibu->wilayah_kerja_id);
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
            'tgl_lahir' => '1991-02-02',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ])->assertRedirect(route('admin.ibu.index'));

        $user = User::where('email', 'dewi@sikia.test')->firstOrFail();
        $ibu = $user->ibu;

        $this->assertNotNull($ibu);
        $this->assertNull($ibu->hpl);
        $this->assertNull($ibu->usia_kehamilan);
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
            'tgl_lahir' => '1992-03-03',
            'wilayah_kerja_id' => $this->wilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
        ])->assertSessionHasErrors(['hpl', 'usia_kehamilan']);
    }

    public function test_admin_cannot_register_ibu_in_another_wilayah(): void
    {
        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);

        $this->actingAs($this->admin())->post('/admin/ibu', [
            'name' => 'Ibu Luar Wilayah',
            'email' => 'luar@sikia.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3525010101920099',
            'tempat_lahir' => 'Gresik',
            'tgl_lahir' => '1992-03-03',
            'wilayah_kerja_id' => $otherWilayah->id,
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'luar@sikia.test']);
    }

    public function test_kelola_ibu_can_filter_by_search_and_status(): void
    {
        $ani = $this->makeUser(RoleEnum::Ibu, $this->wilayah, ['name' => 'Ani Sumberame']);
        $ani->ibu()->update([
            'nik' => '3525010101900011',
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(3)->toDateString(),
            'usia_kehamilan' => 24,
        ]);

        $budi = $this->makeUser(RoleEnum::Ibu, $this->wilayah, [
            'name' => 'Budi Sumberame',
            'status' => UserStatusEnum::Inactive->value,
        ]);
        $budi->ibu()->update([
            'nik' => '3525010101900012',
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ]);

        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);
        $other = $this->makeUser(RoleEnum::Ibu, $otherWilayah, ['name' => 'Citra Banjaran']);
        $other->ibu()->update(['nik' => '3525010101900013']);

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/ibu?search=Ani')
            ->assertOk()
            ->assertSee('Ani Sumberame')
            ->assertDontSee('Citra Banjaran');

        $this->actingAs($admin)->get('/admin/ibu?status='.UserStatusEnum::Inactive->value)
            ->assertOk()
            ->assertSee('Budi Sumberame')
            ->assertDontSee('Ani Sumberame');

        $this->assertNotNull($ani->fresh());
        $this->assertNotNull($other->fresh());
    }

    public function test_admin_can_update_ibu(): void
    {
        $ibuUser = $this->makeUser(RoleEnum::Ibu, $this->wilayah);
        $ibuUser->ibu()->update([
            'nik' => '3525010101900005',
            'tempat_lahir' => 'Gresik',
            'tgl_lahir' => '1993-04-04',
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ]);

        $this->actingAs($this->admin())->put("/admin/ibu/{$ibuUser->id}", [
            'name' => 'Nama Diubah',
            'email' => $ibuUser->email,
            'nik' => '3525010101930004',
            'tempat_lahir' => 'Surabaya',
            'tgl_lahir' => '1993-04-04',
            'status_kehamilan' => StatusKehamilanEnum::Melahirkan->value,
        ])->assertRedirect();

        $this->assertSame('Nama Diubah', $ibuUser->fresh()->name);
        $this->assertSame('3525010101930004', $ibuUser->fresh()->ibu->nik);
    }

    public function test_admin_can_delete_bidan(): void
    {
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);

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
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);
        $kader = $this->makeUser(RoleEnum::Kader, $this->wilayah);
        $ibu = $this->makeUser(RoleEnum::Ibu, $this->wilayah);

        $this->assertTrue($ibu->bidanWilayah->is($bidan));
        $this->assertTrue($ibu->kaderWilayah->is($kader));
    }

    public function test_ibu_sees_mother_data_in_profile(): void
    {
        $ibu = $this->makeUser(RoleEnum::Ibu, $this->wilayah);
        $ibu->ibu()->update([
            'nik' => '3525010101900009',
            'status_kehamilan' => StatusKehamilanEnum::Hamil->value,
            'hpl' => now()->addMonths(2)->toDateString(),
            'usia_kehamilan' => 20,
        ]);

        $this->actingAs($ibu)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Data Ibu')
            ->assertSee('3525010101900009');
    }

    public function test_ibu_dashboard_shows_assigned_staff(): void
    {
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);
        $kader = $this->makeUser(RoleEnum::Kader, $this->wilayah);
        $ibu = $this->makeUser(RoleEnum::Ibu, $this->wilayah);

        $this->actingAs($ibu)->get('/ibu')
            ->assertOk()
            ->assertSee($bidan->name)
            ->assertSee($kader->name);
    }
}
