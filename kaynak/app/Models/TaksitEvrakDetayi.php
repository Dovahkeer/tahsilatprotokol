<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaksitEvrakDetayi extends Model
{
    use HasFactory;

    protected $table = 'taksit_evrak_detaylari';
    
    protected $guarded = [];

    // Bu evrak detayı hangi takside (ana kayda) ait?
    public function taksit()
    {
        return $this->belongsTo(ProtokolTaksit::class, 'protokol_taksit_id');
    }
}