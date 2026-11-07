<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\KegiatanPosyandu;
use App\Models\WilayahKerja;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class DataAndReportSmokeTest extends TestCase
{
    use CreatesUsers;
    use RefreshDatabase;

    private WilayahKerja $wilayah;

    private KegiatanPosyandu $kegiatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->wilayah = $this->makeWilayah();

        $this->kegiatan = KegiatanPosyandu::create([
            'id_wilayah' => $this->wilayah->id,
            'jenis_kegiatan' => 'Posyandu Balita',
            'tanggal' => today(),
            'waktu' => '08:00',
            'lokasi' => 'Balai Desa',
        ]);
    }

    public function test_admin_data_and_report_pages_render(): void
    {
        $admin = $this->makeAdmin($this->wilayah);

        $this->actingAs($admin)->get('/admin/data-ibu')->assertOk();
        $this->actingAs($admin)->get('/admin/data-anak')->assertOk();
        $this->actingAs($admin)->get('/admin/data-anak/create')->assertOk();
        $this->actingAs($admin)->get('/admin/data-posyandu')->assertOk();
        $this->actingAs($admin)->get('/admin/data-posyandu/rekap')->assertOk();
        $this->actingAs($admin)->get("/admin/data-posyandu/{$this->kegiatan->id}")->assertOk();
        $this->actingAs($admin)->get('/admin/laporan')->assertOk();
    }

    public function test_bidan_can_read_data_posyandu_and_master_data(): void
    {
        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);

        $this->actingAs($bidan)->get('/bidan/data-ibu')->assertOk();
        $this->actingAs($bidan)->get('/bidan/data-anak')->assertOk();
        $this->actingAs($bidan)->get('/bidan/data-posyandu')->assertOk();
        $this->actingAs($bidan)->get('/bidan/data-posyandu/rekap')->assertOk();
        $this->actingAs($bidan)->get("/bidan/data-posyandu/{$this->kegiatan->id}")->assertOk();
        $this->actingAs($bidan)->get('/bidan/laporan')->assertOk();
    }

    public function test_kader_can_read_data_posyandu_and_master_data(): void
    {
        $kader = $this->makeUser(RoleEnum::Kader, $this->wilayah);

        $this->actingAs($kader)->get('/kader/data-ibu')->assertOk();
        $this->actingAs($kader)->get('/kader/data-anak')->assertOk();
        $this->actingAs($kader)->get('/kader/data-posyandu')->assertOk();
        $this->actingAs($kader)->get('/kader/data-posyandu/rekap')->assertOk();
        $this->actingAs($kader)->get("/kader/data-posyandu/{$this->kegiatan->id}")->assertOk();
    }

    public function test_activity_from_another_wilayah_is_forbidden(): void
    {
        $otherWilayah = $this->makeWilayah([
            'nama_wilayah' => 'Desa Banjaran, Kec. Driyorejo',
            'kecamatan' => 'Driyorejo',
            'kelurahan' => 'Banjaran',
        ]);
        $otherKegiatan = KegiatanPosyandu::create([
            'id_wilayah' => $otherWilayah->id,
            'jenis_kegiatan' => 'Posyandu Balita',
            'tanggal' => today(),
        ]);

        $bidan = $this->makeUser(RoleEnum::Bidan, $this->wilayah);
        $admin = $this->makeAdmin($this->wilayah);

        $this->actingAs($bidan)->get("/bidan/data-posyandu/{$otherKegiatan->id}")->assertStatus(403);
        $this->actingAs($admin)->get("/admin/data-posyandu/{$otherKegiatan->id}")->assertStatus(403);
    }
}
