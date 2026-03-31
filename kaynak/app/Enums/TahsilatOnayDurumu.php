<?php

namespace App\Enums;

enum TahsilatOnayDurumu: string
{
    case Beklemede = 'beklemede';
    case Onaylandi = 'onaylandi';
    case Reddedildi = 'reddedildi';
    case Iptal = 'iptal';
}
