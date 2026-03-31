<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MuvekkilPrimOrani extends Model
{
    use HasFactory;

    protected $table = 'muvekkil_prim_oranlari';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'prim_orani' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }

    public function muvekkil(): BelongsTo
    {
        return $this->belongsTo(Muvekkil::class);
    }
}
