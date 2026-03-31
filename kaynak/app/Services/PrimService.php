<?php

namespace App\Services;

use App\Enums\TahsilatOnayDurumu;
use App\Models\PrimKademePayOrani;
use App\Models\Muvekkil;
use App\Models\Protokol;
use App\Models\Tahsilat;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PrimService
{
    public function pivotTable(int $ay, int $yil): array
    {
        $start = Carbon::create($yil, $ay, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $muvekkiller = Muvekkil::query()
            ->with('primOrani')
            ->orderBy('ad')
            ->get(['id', 'ad']);

        $muvekkilPrimOranlari = $muvekkiller
            ->mapWithKeys(fn (Muvekkil $muvekkil) => [
                (string) $muvekkil->id => (float) (
                    $muvekkil->primOrani && $muvekkil->primOrani->aktif
                        ? $muvekkil->primOrani->prim_orani
                        : 0
                ),
            ])
            ->all();

        $kademePayOranlari = PrimKademePayOrani::query()
            ->where('aktif', true)
            ->get()
            ->keyBy(fn (PrimKademePayOrani $oran) => $oran->ust_kademe.'|'.$oran->alt_kademe);

        $rows = collect();

        Tahsilat::query()
            ->with(['protokol.hacizciler', 'muvekkil'])
            ->where('onay_durumu', TahsilatOnayDurumu::Onaylandi->value)
            ->whereBetween('tahsilat_tarihi', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('protokol_id')
            ->get()
            ->each(function (Tahsilat $tahsilat) use (&$rows, $kademePayOranlari, $muvekkilPrimOranlari) {
                $protokol = $tahsilat->protokol;
                if (! $protokol) {
                    return;
                }

                $dagitim = $this->distributionMap($protokol, $kademePayOranlari);
                foreach ($dagitim as $hacizciId => $oran) {
                    $satir = $rows->get($hacizciId, [
                        'hacizci_id' => (string) $hacizciId,
                        'hacizci_ad' => $protokol->hacizciler->firstWhere('id', $hacizciId)?->ad_soyad ?? 'Bilinmeyen',
                        'kademe' => $protokol->hacizciler->firstWhere('id', $hacizciId)?->kademe ?? '-',
                        'toplam_prime_esas_tahsilat' => 0.0,
                        'toplam_prim_tutari' => 0.0,
                    ]);

                    $pay = round(Money::float(Money::mul($tahsilat->tutar, Money::div((string) $oran, '100', 6), 6)), 2);
                    $muvekkilKey = (string) $tahsilat->muvekkil_id;
                    $primOrani = (float) ($muvekkilPrimOranlari[$muvekkilKey] ?? 0);
                    $primTutari = round($pay * ($primOrani / 100), 2);
                    $satir[$muvekkilKey] = round(($satir[$muvekkilKey] ?? 0) + $pay, 2);
                    $satir['toplam_prime_esas_tahsilat'] = round(($satir['toplam_prime_esas_tahsilat'] ?? 0) + $pay, 2);
                    $satir['toplam_prim_tutari'] = round(($satir['toplam_prim_tutari'] ?? 0) + $primTutari, 2);
                    $rows->put($hacizciId, $satir);
                }
            });

        return [
            'muvekkiller' => $muvekkiller->map(fn (Muvekkil $muvekkil) => [
                'id' => (string) $muvekkil->id,
                'ad' => $muvekkil->ad,
            ])->values()->all(),
            'prime_esas_pivot_data' => $rows->sortBy('hacizci_ad')->values()->all(),
        ];
    }

    private function distributionMap(Protokol $protokol, Collection $kademePayOranlari): array
    {
        $hacizciler = $protokol->hacizciler;
        $count = $hacizciler->count();
        if ($count === 0) {
            return [];
        }

        $manual = $hacizciler->every(fn ($hacizci) => $hacizci->pivot->pay_orani !== null);
        if ($manual) {
            return $hacizciler->mapWithKeys(fn ($hacizci) => [
                $hacizci->id => (float) $hacizci->pivot->pay_orani,
            ])->all();
        }

        if ($count === 1) {
            return [
                $hacizciler->first()->id => 100.0,
            ];
        }

        if ($count !== 2) {
            $oran = round(100 / $count, 2);

            return $hacizciler->mapWithKeys(fn ($hacizci) => [
                $hacizci->id => $oran,
            ])->all();
        }

        $ilk = $hacizciler->values()->get(0);
        $ikinci = $hacizciler->values()->get(1);

        if (! $ilk || ! $ikinci) {
            return [];
        }

        if ($ilk->pivot->haciz_turu !== $ikinci->pivot->haciz_turu) {
            return [
                $ilk->id => 50.0,
                $ikinci->id => 50.0,
            ];
        }

        $ilkKademe = $this->kademeSirasi($ilk->kademe);
        $ikinciKademe = $this->kademeSirasi($ikinci->kademe);

        if ($ilkKademe === null || $ikinciKademe === null || $ilkKademe === $ikinciKademe) {
            return [
                $ilk->id => 50.0,
                $ikinci->id => 50.0,
            ];
        }

        [$ust, $alt] = $ilkKademe < $ikinciKademe
            ? [$ilk, $ikinci]
            : [$ikinci, $ilk];

        $kayit = $kademePayOranlari->get($ust->kademe.'|'.$alt->kademe);

        if (! $kayit) {
            return [
                $ilk->id => 50.0,
                $ikinci->id => 50.0,
            ];
        }

        return [
            $ust->id => (float) $kayit->ust_kademe_orani,
            $alt->id => (float) $kayit->alt_kademe_orani,
        ];
    }

    private function kademeSirasi(?string $kademe): ?int
    {
        return match ($kademe) {
            'kademe_1' => 1,
            'kademe_2' => 2,
            'kademe_3' => 3,
            default => null,
        };
    }
}
