<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case UserView = 'user.view';
    case UserCreate = 'user.create';
    case UserUpdate = 'user.update';
    case UserDelete = 'user.delete';
    case UserActivate = 'user.activate';
    case UserAssignRole = 'user.assign-role';

    public function label(): string
    {
        return match ($this) {
            self::UserView => 'Melihat user',
            self::UserCreate => 'Menambah user',
            self::UserUpdate => 'Mengubah user',
            self::UserDelete => 'Menghapus user',
            self::UserActivate => 'Mengaktifkan / menonaktifkan user',
            self::UserAssignRole => 'Mengatur role user',
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
