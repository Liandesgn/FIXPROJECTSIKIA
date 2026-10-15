<?php

namespace App\Enums;

enum StatusKehamilanEnum: string
{
    case Hamil = 'hamil';
    case Melahirkan = 'melahirkan';

    public function label(): string
    {
        return match ($this) {
            self::Hamil => 'Hamil',
            self::Melahirkan => 'Melahirkan',
        };
    }

    public function sedangHamil(): bool
    {
        return $this === self::Hamil;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
