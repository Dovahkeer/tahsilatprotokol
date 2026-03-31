<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtokolNumaraSayaci extends Model
{
    use HasFactory;

    protected $table = 'protokol_numara_sayaclari';

    protected $guarded = [];
}
