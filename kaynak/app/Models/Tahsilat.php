<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tahsilat extends Model
{
    use HasFactory;

    protected $table = 'tahsilatlar';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'protokolsuz' => 'boolean',
            'tahsilat_tarihi' => 'date',
            'tutar' => 'decimal:2',
            'tahsilat_birimleri' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (): void {
            throw new LogicException('Tahsilat kayitlari uygulama icinden silinemez.');
        });
    }

    public function muvekkil(): BelongsTo
    {
        return $this->belongsTo(Muvekkil::class);
    }

    public function protokol(): BelongsTo
    {
        return $this->belongsTo(Protokol::class);
    }

    public function protokolTaksit(): BelongsTo
    {
        return $this->belongsTo(ProtokolTaksit::class, 'protokol_taksit_id');
    }

    public function dekontlar(): HasMany
    {
        return $this->hasMany(TahsilatDekontu::class);
    }

    public function olusturan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function guncelleyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function onaylayan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'onaylayan_user_id');
    }

    public function reddeden(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reddeden_user_id');
    }

    public function iptalEden(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iptal_eden_user_id');
    }
}
