<?php

namespace App\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case OrangTua = 'orang_tua';
    case Bidan = 'bidan';
    case Kader = 'kader';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::OrangTua => 'Orang Tua',
            self::Bidan => 'Bidan',
            self::Kader => 'Kader',
        };
    }

    /**
     * Role yang boleh dikelola (dibuat/diubah) oleh Admin melalui Manajemen User.
     *
     * @return array<int, string>
     */
    public static function manageable(): array
    {
        return [
            self::OrangTua->value,
            self::Bidan->value,
            self::Kader->value,
        ];
    }

    /**
     * Nama route dashboard tujuan setelah berhasil login.
     */
    public function dashboard(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::OrangTua => 'orang-tua.dashboard',
            self::Bidan => 'bidan.dashboard',
            self::Kader => 'kader.dashboard',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
