<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahsilatDekontu extends Model
{
    use HasFactory;

    protected $table = 'tahsilat_dekontlari';

    protected $guarded = [];

    public function tahsilat(): BelongsTo
    {
        return $this->belongsTo(Tahsilat::class);
    }

    public function yukleyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
