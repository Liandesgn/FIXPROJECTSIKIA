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
            RoleEnum::SuperAdmin => self::superAdmin(),
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
   
private static function superAdmin(): array
{
    return [
        [
            'heading' => 'Utama',
            'items' => [
                self::link('Dashboard', 'superadmin.dashboard'),
            ],
        ],
        [
            'heading' => 'Manajemen Admin',
            'items' => [
                self::link('Kelola Admin', 'superadmin.admins.index', 'superadmin.admins.*'),
            ],
        ],
        [
            'heading' => 'Manajemen User',
            'items' => [
                self::link('Kelola Bidan', 'superadmin.bidan.index', 'superadmin.bidan.*'),
                self::link('Kelola Kader', 'superadmin.kader.index', 'superadmin.kader.*'),
                self::link('Kelola Ibu', 'superadmin.ibu.index', 'superadmin.ibu.*'),
            ],
        ],
        [
            'heading' => 'Master Data',
            'items' => [
                self::link('Kelola Wilayah', 'superadmin.wilayah-kerja.index', 'superadmin.wilayah-kerja.*'),
            ],
        ],
        [
            'heading' => 'Kelola Edukasi',
            'items' => [
                self::link('Kelola Edukasi', 'panel.module', null, [
                    'module' => 'edukasi',
                ]),
            ],
        ],
        [
            'heading' => 'Laporan',
            'items' => [
                self::link('Laporan', 'superadmin.laporan.index', 'superadmin.laporan.*'),
            ],
        ],
        [
            'heading' => 'Akses & Hak',
            'items' => [
                self::link('Role', 'superadmin.roles.index', 'superadmin.roles.*'),
                self::link('Permission', 'superadmin.permissions.index', 'superadmin.permissions.*'),
            ],
        ],
                ];
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
                'heading' => 'Data',
                'items' => [
                    self::link('Data Ibu', 'admin.data-ibu.index', 'admin.data-ibu.*'),
                    self::link('Data Anak', 'admin.data-anak.index', 'admin.data-anak.*'),
                    self::link('Data Posyandu', 'admin.data-posyandu.index', 'admin.data-posyandu.*'),
                ],
            ],
            [
                'heading' => 'Laporan',
                'items' => [
                    self::link('Laporan dan Rekapitulasi', 'admin.laporan.index', 'admin.laporan.*'),
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
                'heading' => 'Menu',
                'items' => [
                    self::link('Data Ibu', 'bidan.data-ibu.index', 'bidan.data-ibu.*'),
                    self::link('Data Anak', 'bidan.data-anak.index', 'bidan.data-anak.*'),
                    self::soon('Pemeriksaan Pasien', 'pemeriksaan-pasien'),
                    self::soon('Posyandu', 'posyandu'),
                    self::soon('Riwayat Pemeriksaan', 'riwayat-pemeriksaan'),
                    self::soon('Konsultasi', 'konsultasi'),
                    self::soon('Jadwal', 'jadwal'),
                    self::link('Laporan Wilayah', 'bidan.laporan.index', 'bidan.laporan.*'),
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
                'heading' => 'Data',
                'items' => [
                    self::link('Data Ibu', 'kader.data-ibu.index', 'kader.data-ibu.*'),
                    self::link('Data Anak', 'kader.data-anak.index', 'kader.data-anak.*'),
                    self::soon('Jadwal Posyandu', 'jadwal-posyandu'),
                    self::soon('Pendataan Posyandu', 'pendataan-posyandu'),
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
                    self::link('Dashboard', 'ibu.dashboard'),
                ],
            ],
            [
                'heading' => 'Data',
                'items' => [
                    self::link('Data Ibu', 'ibu.data-ibu.index', 'ibu.data-ibu.*'),
                    self::link('Data Anak', 'ibu.data-anak.index', 'ibu.data-anak.*'),
                    self::soon('Jadwal Pemeriksaan', 'jadwal-pemeriksaan'),
                    self::soon('Konsultasi Bidan', 'konsultasi'),
                    self::soon('Edukasi Kesehatan', 'edukasi'),
                    self::soon('Jadwal', 'jadwal')
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
   private static function link(
    string $label,
    string $route,
    ?string $match = null,
    array $params = []
): array {
    return [
        'label' => $label,
        'route' => $route,
        'params' => $params,
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
