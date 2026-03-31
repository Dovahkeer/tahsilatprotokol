<?php

namespace App\Services;

use App\Models\ProtokolNumaraSayaci;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProtokolNoService
{
    public function generate(CarbonInterface $tarih): string
    {
        $yil = (int) $tarih->format('Y');

        return DB::transaction(function () use ($yil) {
            $sayac = ProtokolNumaraSayaci::query()
                ->where('yil', $yil)
                ->lockForUpdate()
                ->first();

            if (! $sayac) {
                try {
                    $sayac = ProtokolNumaraSayaci::query()->create([
                        'yil' => $yil,
                        'son_sira' => 0,
                    ]);
                } catch (QueryException) {
                    $sayac = ProtokolNumaraSayaci::query()
                        ->where('yil', $yil)
                        ->lockForUpdate()
                        ->firstOrFail();
                }
            }

            $sayac->increment('son_sira');
            $sayac->refresh();

            return sprintf('PRT-%d-%06d', $yil, $sayac->son_sira);
        }, 3);
    }
}
