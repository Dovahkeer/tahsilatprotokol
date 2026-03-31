<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hacizci extends Model
{
    use HasFactory;

    protected $table = 'hacizciler';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function protokoller(): BelongsToMany
    {
        return $this->belongsToMany(Protokol::class, 'protokol_hacizci')
            ->withPivot(['haciz_turu', 'pay_orani']);
    }
}
