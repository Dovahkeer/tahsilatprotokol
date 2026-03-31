<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimKademePayOrani extends Model
{
    use HasFactory;

    protected $table = 'prim_kademe_pay_oranlari';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ust_kademe_orani' => 'decimal:2',
            'alt_kademe_orani' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }
}
