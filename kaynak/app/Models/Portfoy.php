<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfoy extends Model
{
    use HasFactory;

    protected $table = 'portfoyler';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function muvekkil(): BelongsTo
    {
        return $this->belongsTo(Muvekkil::class);
    }

    public function protokoller(): HasMany
    {
        return $this->hasMany(Protokol::class);
    }
}
