<?php

namespace App\Support;

use App\Enums\RoleEnum;
use App\Models\User;

class Navigation
{
    /**
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    public static function for(User $user): array
    {
        return match ($user->primaryRole()) {
            RoleEnum::Admin => self::admin(),
            RoleEnum::Bidan => self::bidan(),
            RoleEnum::Kader => self::kader(),
            RoleEnum::OrangTua => self::orangTua(),
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function isActive(array $item): bool
    {
        $request = request();

        if (($item['route'] ?? null) === 'panel.module') {
            return $request->routeIs('panel.module')
                && $request->route('module') === ($item['params']['module'] ?? null);
        }

        return $request->routeIs($item['match'] ?? $item['route']);
    }

    /**
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    private static function admin(): array
    {
        return [
            [
                'heading' => 'Utama',
                'items' => [
                    self::link('Dashboard', 'admin.dashboard'),
                ],
            ],
            [
                'heading' => 'Manajemen',
                'items' => [
                    self::link('Manajemen User', 'admin.users.index', 'admin.users.*'),
                    self::link('Kelola Orang Tua', 'panel.kelola-orang-tua.index', 'panel.kelola-orang-tua.*'),
                ],
            ],
            [
                'heading' => 'Master Data',
                'items' => [
                    self::soon('Data Ibu', 'data-ibu'),
                    self::soon('Data Anak', 'data-anak'),
                    self::soon('Data Posyandu', 'data-posyandu'),
                    self::soon('Wilayah Kerja', 'wilayah-kerja'),
                    self::soon('Jadwal Kegiatan', 'jadwal-kegiatan'),
                    self::soon('Laporan', 'laporan'),
                ],
            ],
            [
                'heading' => 'Akun',
                'items' => [
                    self::link('Profil', 'profile.edit', 'profile.*'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    private static function bidan(): array
    {
        return [
            [
                'heading' => 'Utama',
                'items' => [
                    self::link('Dashboard', 'bidan.dashboard'),
                ],
            ],
            [
                'heading' => 'Wilayah Kerja',
                'items' => [
                    self::link('Kelola Orang Tua', 'panel.kelola-orang-tua.index', 'panel.kelola-orang-tua.*'),
                    self::soon('Data Ibu', 'data-ibu'),
                    self::soon('Data Anak', 'data-anak'),
                ],
            ],
            [
                'heading' => 'Pemeriksaan',
                'items' => [
                    self::soon('Pemeriksaan Ibu Hamil', 'pemeriksaan-ibu'),
                    self::soon('Pemeriksaan Anak & Balita', 'pemeriksaan-anak'),
                    self::soon('Riwayat Pemeriksaan', 'riwayat-pemeriksaan'),
                    self::soon('Monitoring Status Kesehatan', 'monitoring-status'),
                    self::soon('Grafik Perkembangan', 'grafik-perkembangan'),
                ],
            ],
            [
                'heading' => 'Layanan',
                'items' => [
                    self::soon('Konsultasi Bidan', 'konsultasi'),
                    self::soon('Jadwal Pemeriksaan', 'jadwal-pemeriksaan'),
                    self::soon('Jadwal Posyandu', 'jadwal-posyandu'),
                    self::soon('Laporan Kesehatan Wilayah', 'laporan-wilayah'),
                ],
            ],
            [
                'heading' => 'Akun',
                'items' => [
                    self::link('Profil', 'profile.edit', 'profile.*'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    private static function kader(): array
    {
        return [
            [
                'heading' => 'Utama',
                'items' => [
                    self::link('Dashboard', 'kader.dashboard'),
                ],
            ],
            [
                'heading' => 'Posyandu',
                'items' => [
                    self::soon('Jadwal Posyandu', 'jadwal-posyandu'),
                    self::soon('Notifikasi Sistem', 'notifikasi'),
                    self::soon('Pendataan Kegiatan', 'pendataan-posyandu'),
                ],
            ],
            [
                'heading' => 'Pengukuran',
                'items' => [
                    self::link('Kelola Orang Tua', 'panel.kelola-orang-tua.index', 'panel.kelola-orang-tua.*'),
                    self::soon('Cari Data Ibu', 'cari-ibu'),
                    self::soon('Data Anak', 'data-anak'),
                    self::soon('Input Hasil Pengukuran', 'input-pengukuran'),
                ],
            ],
            [
                'heading' => 'Akun',
                'items' => [
                    self::link('Profil', 'profile.edit', 'profile.*'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    private static function orangTua(): array
    {
        return [
            [
                'heading' => 'Utama',
                'items' => [
                    self::link('Dashboard', 'orang-tua.dashboard'),
                ],
            ],
            [
                'heading' => 'Data',
                'items' => [
                    self::soon('Data Ibu', 'data-ibu'),
                    self::soon('Data Anak', 'data-anak'),
                ],
            ],
            [
                'heading' => 'Pemeriksaan',
                'items' => [
                    self::soon('Riwayat Pemeriksaan Ibu', 'riwayat-ibu'),
                    self::soon('Riwayat Pemeriksaan Anak', 'riwayat-anak'),
                ],
            ],
            [
                'heading' => 'Layanan',
                'items' => [
                    self::soon('Konsultasi Bidan', 'konsultasi'),
                    self::soon('Riwayat Konsultasi', 'riwayat-konsultasi'),
                    self::soon('Edukasi Kesehatan', 'edukasi'),
                ],
            ],
            [
                'heading' => 'Jadwal & Info',
                'items' => [
                    self::soon('Jadwal Pemeriksaan', 'jadwal-pemeriksaan'),
                    self::soon('Notifikasi Sistem', 'notifikasi'),
                ],
            ],
            [
                'heading' => 'Akun',
                'items' => [
                    self::link('Profil', 'profile.edit', 'profile.*'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function link(string $label, string $route, ?string $match = null): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'params' => [],
            'match' => $match ?? $route,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function soon(string $label, string $slug): array
    {
        return [
            'label' => $label,
            'route' => 'panel.module',
            'params' => ['module' => $slug],
            'match' => 'panel.module',
        ];
    }
}
