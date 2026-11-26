<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandarTBU extends Model
{
    protected $table = 'standar_tbu';

    protected $fillable = [
        'jenis_kelamin',
        'umur_bulan',
        'median',
        'sd_minus_1',
        'sd_plus_1',
    ];

    protected function casts(): array
    {
        return [
            'umur_bulan' => 'integer',
            'median' => 'decimal:2',
            'sd_minus_1' => 'decimal:2',
            'sd_plus_1' => 'decimal:2',
        ];
    }
}
