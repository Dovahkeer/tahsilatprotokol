<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzlenceGecmisTahsilati extends Model
{
    use HasFactory;

    protected $table = 'izlence_gecmis_tahsilatlari';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tahsilat_tarihi' => 'date',
            'tutar' => 'decimal:2',
            'source_payload' => 'array',
        ];
    }

    public function muvekkil(): BelongsTo
    {
        return $this->belongsTo(Muvekkil::class);
    }
}
