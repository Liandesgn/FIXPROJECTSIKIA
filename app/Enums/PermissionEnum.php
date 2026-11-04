<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case UserView = 'user.view';
    case UserCreate = 'user.create';
    case UserUpdate = 'user.update';
    case UserDelete = 'user.delete';
    case UserActivate = 'user.activate';

    case AdminView = 'admin.view';
    case AdminCreate = 'admin.create';
    case AdminUpdate = 'admin.update';
    case AdminDelete = 'admin.delete';

    case UserRoleView = 'userrole.view';
    case UserRoleUpdate = 'userrole.update';

    case UserPermissionView = 'userpermission.view';
    case UserPermissionCreate = 'userpermission.create';
    case UserPermissionUpdate = 'userpermission.update';
    case UserPermissionDelete = 'userpermission.delete';

    case WilayahView = 'wilayah.view';
    case WilayahCreate = 'wilayah.create';
    case WilayahUpdate = 'wilayah.update';
    case WilayahDelete = 'wilayah.delete';

    case IbuView = 'ibu.view';
    case IbuUpdate = 'ibu.update';
    case AnakView = 'anak.view';
    case AnakCreate = 'anak.create';
    case AnakUpdate = 'anak.update';
    case PosyanduView = 'posyandu.view';
    case LaporanView = 'laporan.view';

    public function label(): string
    {
        return match ($this) {
            self::UserView => 'Melihat user',
            self::UserCreate => 'Menambah user',
            self::UserUpdate => 'Mengubah user',
            self::UserDelete => 'Menghapus user',
            self::UserActivate => 'Mengaktifkan / menonaktifkan user',
            self::AdminView => 'Melihat admin',
            self::AdminCreate => 'Menambah admin',
            self::AdminUpdate => 'Mengubah admin',
            self::AdminDelete => 'Menghapus admin',
            self::UserRoleView => 'Melihat role',
            self::UserRoleUpdate => 'Mengubah role',
            self::UserPermissionView => 'Melihat permission',
            self::UserPermissionCreate => 'Menambah permission',
            self::UserPermissionUpdate => 'Mengubah permission',
            self::UserPermissionDelete => 'Menghapus permission',
            self::WilayahView => 'Melihat wilayah kerja',
            self::WilayahCreate => 'Menambah wilayah kerja',
            self::WilayahUpdate => 'Mengubah wilayah kerja',
            self::WilayahDelete => 'Menghapus wilayah kerja',
            self::IbuView => 'Melihat data ibu',
            self::IbuUpdate => 'Mengubah data ibu',
            self::AnakView => 'Melihat data anak',
            self::AnakCreate => 'Menambah data anak',
            self::AnakUpdate => 'Mengubah data anak',
            self::PosyanduView => 'Melihat data posyandu',
            self::LaporanView => 'Melihat laporan',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Permission yang diberikan kepada Admin wilayah.
     *
     * @return array<int, string>
     */
    public static function adminValues(): array
    {
        return [
            self::UserView->value,
            self::UserCreate->value,
            self::UserUpdate->value,
            self::UserDelete->value,
            self::UserActivate->value,
            self::IbuView->value,
            self::IbuUpdate->value,
            self::AnakView->value,
            self::AnakCreate->value,
            self::AnakUpdate->value,
            self::PosyanduView->value,
            self::LaporanView->value,
        ];
    }
}