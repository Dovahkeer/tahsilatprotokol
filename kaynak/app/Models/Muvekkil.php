<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Muvekkil extends Model
{
    use HasFactory;

    protected $table = 'muvekkiller';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function portfoyler(): HasMany
    {
        return $this->hasMany(Portfoy::class);
    }

    public function protokoller(): HasMany
    {
        return $this->hasMany(Protokol::class);
    }

    public function tahsilatlar(): HasMany
    {
        return $this->hasMany(Tahsilat::class);
    }

    public function primOrani(): HasOne
    {
        return $this->hasOne(MuvekkilPrimOrani::class);
    }
}
