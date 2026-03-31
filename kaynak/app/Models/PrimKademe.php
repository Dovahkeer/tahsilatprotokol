<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimKademe extends Model
{
    use HasFactory;

    protected $table = 'prim_kademeler';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'varsayilan_prim_orani' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }
}
