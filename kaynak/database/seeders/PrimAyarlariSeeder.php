<?php

namespace Database\Seeders;

use App\Models\PrimKademe;
use App\Models\PrimKademeAsamasi;
use App\Models\PrimKademePayOrani;
use App\Models\Hacizci;
use Illuminate\Database\Seeder;

class PrimAyarlariSeeder extends Seeder
{
    public function run(): void
    {
        $kademeler = config('tahsilat.varsayilan_kademeler', []);
        $kademeKeys = collect($kademeler)->pluck('kademe')->all();
        $sonKademe = collect($kademeKeys)->last() ?: 'kademe_1';

        Hacizci::query()
            ->whereNotIn('kademe', $kademeKeys)
            ->update(['kademe' => $sonKademe]);

        PrimKademeAsamasi::query()
            ->whereNotIn('kademe', $kademeKeys)
            ->delete();

        PrimKademePayOrani::query()
            ->whereNotIn('ust_kademe', $kademeKeys)
            ->orWhereNotIn('alt_kademe', $kademeKeys)
            ->delete();

        PrimKademe::query()
            ->whereNotIn('kademe', $kademeKeys)
            ->delete();

        foreach ($kademeler as $kademe) {
            PrimKademe::updateOrCreate(
                ['kademe' => $kademe['kademe']],
                [
                    'kademe_adi' => $kademe['kademe_adi'],
                    'varsayilan_prim_orani' => $kademe['varsayilan_prim_orani'],
                    'aktif' => true,
                ],
            );

            for ($asama = 1; $asama <= 3; $asama++) {
                PrimKademeAsamasi::updateOrCreate(
                    [
                        'kademe' => $kademe['kademe'],
                        'asama_no' => $asama,
                    ],
                    [
                        'esik_tutari' => match ($asama) {
                            1 => '0.00',
                            2 => '50000.00',
                            default => '150000.00',
                        },
                        'prim_orani' => match ($asama) {
                            1 => $kademe['varsayilan_prim_orani'],
                            2 => bcadd((string) $kademe['varsayilan_prim_orani'], '1.00', 2),
                            default => bcadd((string) $kademe['varsayilan_prim_orani'], '2.00', 2),
                        },
                        'aktif' => true,
                    ],
                );
            }
        }

        $payOranlari = [
            ['ust_kademe' => 'kademe_1', 'alt_kademe' => 'kademe_2', 'ust_kademe_orani' => '60.00', 'alt_kademe_orani' => '40.00'],
            ['ust_kademe' => 'kademe_1', 'alt_kademe' => 'kademe_3', 'ust_kademe_orani' => '80.00', 'alt_kademe_orani' => '20.00'],
            ['ust_kademe' => 'kademe_2', 'alt_kademe' => 'kademe_3', 'ust_kademe_orani' => '60.00', 'alt_kademe_orani' => '40.00'],
        ];

        foreach ($payOranlari as $kayit) {
            PrimKademePayOrani::updateOrCreate(
                [
                    'ust_kademe' => $kayit['ust_kademe'],
                    'alt_kademe' => $kayit['alt_kademe'],
                ],
                $kayit + ['aktif' => true],
            );
        }
    }
}
