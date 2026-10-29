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
            RoleEnum::Ibu => self::orangTua(),
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
                'heading' => 'Manajemen User',
                'items' => [
                    self::link('Kelola Bidan', 'admin.bidan.index', 'admin.bidan.*'),
                    self::link('Kelola Kader', 'admin.kader.index', 'admin.kader.*'),
                    self::link('Kelola Ibu', 'admin.ibu.index', 'admin.ibu.*'),
                ],
            ],
            [
                'heading' => 'Master Data',
                'items' => [
                    self::soon('Data Ibu', 'data-ibu'),
                    self::soon('Data Anak', 'data-anak'),
                    self::soon('Data Posyandu', 'data-posyandu'),
                    self::soon('Data Wilayah Kerja', 'wilayah-kerja'),
                    self::soon('Kelola Jadwal', 'jadwal-kegiatan'),
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

   private static function bidan(): array
{
    return [
        ['heading' => 'Utama', 'items' => [
            self::link('Dashboard', 'bidan.dashboard'),
        ]],
        ['heading' => 'Menu', 'items' => [
            self::link('Data Ibu', 'bidan.data-ibu.index', 'bidan.data-ibu.*'),
            self::link('Data Anak', 'bidan.data-anak.index', 'bidan.data-anak.*'),
            self::soon('Jadwal', 'jadwal-pemeriksaan'),
            self::soon('Laporan', 'laporan'),
        ]],
        ['heading' => 'Akun', 'items' => [
            self::link('Profil', 'profile.edit', 'profile.*'),
        ]],
    ];
}

private static function kader(): array
{
    return [
        ['heading' => 'Utama', 'items' => [
            self::link('Dashboard', 'kader.dashboard'),
        ]],
        ['heading' => 'Data', 'items' => [
            self::link('Data Ibu', 'kader.data-ibu.index', 'kader.data-ibu.*'),
            self::link('Data Anak', 'kader.data-anak.index', 'kader.data-anak.*'),
        ]],
        ['heading' => 'Posyandu', 'items' => [
            self::soon('Jadwal Posyandu', 'jadwal-posyandu'),
            self::soon('Pendataan Kegiatan', 'pendataan-posyandu'),
        ]],
        ['heading' => 'Pengukuran', 'items' => [
            self::soon('Input Hasil Pengukuran', 'input-pengukuran'),
        ]],
        ['heading' => 'Akun', 'items' => [
            self::link('Profil', 'profile.edit', 'profile.*'),
        ]],
    ];
}

private static function orangTua(): array
{
    return [
        ['heading' => 'Utama', 'items' => [
            self::link('Dashboard', 'ibu.dashboard'),
        ]],
        ['heading' => 'Data', 'items' => [
            self::link('Data Ibu', 'ibu.data-ibu.index', 'ibu.data-ibu.*'),
            self::link('Data Anak', 'ibu.data-anak.index', 'ibu.data-anak.*'),
        ]],
        ['heading' => 'Layanan', 'items' => [
            self::soon('Konsultasi Bidan', 'konsultasi'),
            self::soon('Edukasi Kesehatan', 'edukasi'),
        ]],
        ['heading' => 'Jadwal & Info', 'items' => [
            self::soon('Jadwal Pemeriksaan', 'jadwal-pemeriksaan'),
        ]],
        ['heading' => 'Akun', 'items' => [
            self::link('Profil', 'profile.edit', 'profile.*'),
        ]],
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