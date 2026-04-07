<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; //<-- SADECE BU SATIRI EKLE

class ProtokolTaksit extends Model
{
    use HasFactory;

    protected $table = 'protokol_taksitler';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'taksit_tarihi' => 'date',
            'taksit_tutari' => 'decimal:2',
            'odenen_tutar' => 'decimal:2',
        ];
    }

    public function protokol(): BelongsTo
    {
        return $this->belongsTo(Protokol::class);
    }

    public function tahsilatlar(): HasMany
    {
        return $this->hasMany(Tahsilat::class, 'protokol_taksit_id');
    }

    // <-- YENİ EKLENEN KISIM BURADAN BAŞLIYOR -->
    public function evrakDetayi(): HasOne
    {
        return $this->hasOne(TaksitEvrakDetayi::class, 'protokol_taksit_id');
    }
}
