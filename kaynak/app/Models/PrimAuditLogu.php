<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrimAuditLogu extends Model
{
    use HasFactory;

    protected $table = 'prim_audit_loglari';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'hedef_anahtar' => 'array',
            'eski_deger' => 'array',
            'yeni_deger' => 'array',
        ];
    }

    public function degistiren(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
