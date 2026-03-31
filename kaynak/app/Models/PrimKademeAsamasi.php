<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimKademeAsamasi extends Model
{
    use HasFactory;

    protected $table = 'prim_kademe_asamalari';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'esik_tutari' => 'decimal:2',
            'prim_orani' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }
}
