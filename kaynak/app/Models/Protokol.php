<?php

namespace App\Models;

use LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Protokol extends Model
{
    use HasFactory;

    protected $table = 'protokoller';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'protokol_tarihi' => 'date',
            'pesinat' => 'decimal:2',
            'toplam_protokol_tutari' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (): void {
            throw new LogicException('Protokol kayitlari uygulama icinden silinemez.');
        });
    }

    public function muvekkil(): BelongsTo
    {
        return $this->belongsTo(Muvekkil::class);
    }

    public function portfoy(): BelongsTo
    {
        return $this->belongsTo(Portfoy::class);
    }

    public function taksitler(): HasMany
    {
        return $this->hasMany(ProtokolTaksit::class)->orderBy('taksit_no');
    }

    public function hacizciler(): BelongsToMany
    {
        return $this->belongsToMany(Hacizci::class, 'protokol_hacizci')
            ->withPivot(['haciz_turu', 'pay_orani']);
    }

    public function tahsilatlar(): HasMany
    {
        return $this->hasMany(Tahsilat::class);
    }

    public function olusturan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function guncelleyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
