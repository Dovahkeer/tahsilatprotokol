<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahsilatYetkiliKullanici extends Model
{
    use HasFactory;

    protected $table = 'tahsilat_yetkili_kullanicilar';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tahsilat_olusturabilir' => 'boolean',
            'protokol_olusturabilir' => 'boolean',
            'protokol_duzenleyebilir' => 'boolean',
            'toplu_protokol_ekleyebilir' => 'boolean',
            'tahsilat_takip_sorumlusu' => 'boolean',
            'aktif' => 'boolean',
            'tab_permissions' => 'array',
            
            // YENİ EKLENEN SATIR: Veritabanındaki JSON verisini diziye (array) dönüştürür
            'sorumlu_muvekkiller' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}