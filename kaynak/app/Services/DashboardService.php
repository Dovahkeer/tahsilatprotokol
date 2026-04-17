<?php

namespace App\Services;

use App\Enums\TahsilatOnayDurumu;
use App\Models\IzlenceGecmisTahsilati;
use App\Models\Protokol;
use App\Models\ProtokolTaksit;
use App\Models\Tahsilat;
use App\Support\Money;
use App\Support\NameNormalizer;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly ProtokolService $protokolService,
    ) {
    }

    public function build(): array
    {
        $today = now()->startOfDay();
        $currentMonth = $today->copy()->startOfMonth();
        $previousMonth = $currentMonth->copy()->subMonth();
        $twoMonthsAgo = $currentMonth->copy()->subMonths(2);

        $merged = $this->mergedTahsilatDataset();
        $approved = $this->approvedTahsilatDataset();

        $bugun = $approved->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameDay($today));
        $buAy = $merged->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameMonth($today));
        $lastTwelveMonths = $merged->filter(fn (array $row) => $row['tahsilat_tarihi']->gte($currentMonth->copy()->subMonths(11)));

        $yillikAylikOrtalama = Money::div(
            $this->sumRows($lastTwelveMonths),
            '12',
            2,
        );

        $aylikFarkYuzde = Money::cmp($yillikAylikOrtalama, '0') === 0
            ? null
            : round(((Money::float($this->sumRows($buAy)) - Money::float($yillikAylikOrtalama)) / Money::float($yillikAylikOrtalama)) * 100, 2);

        return [
            'bugun_tahsilat_tutari' => Money::float($this->sumRows($bugun)),
            'aylik_tahsilat_tutari' => Money::float($this->sumRows($buAy)),
            'yillik_aylik_ortalama_tahsilat_tutari' => Money::float($yillikAylikOrtalama),
            'yil_ortalamasina_gore_aylik_fark_yuzde' => $aylikFarkYuzde,
            'bekleyen_tahsilat_sayisi' => Tahsilat::query()->where('onay_durumu', TahsilatOnayDurumu::Beklemede->value)->count(),
            'toplam_tahsilat_sayisi' => Tahsilat::query()->where('onay_durumu', '!=', TahsilatOnayDurumu::Iptal->value)->count(),
            'muvekkil_bazli_bu_ay_beklenti' => $this->buildExpectationSummary($currentMonth),
            'is_gunu_analizi' => [
                'bu_ay' => $this->buildWorkingDayRow($merged, $currentMonth),
                'gecen_ay' => $this->buildWorkingDayRow($merged, $previousMonth),
                'iki_ay_once' => $this->buildWorkingDayRow($merged, $twoMonthsAgo),
            ],
            'is_gunu_kumulatif_analizi' => [
                'bu_ay' => $this->buildCumulativeWorkingDayRow($merged, $currentMonth, $today),
                'gecen_ay' => $this->buildCumulativeWorkingDayRow($merged, $previousMonth, $today),
                'iki_ay_once' => $this->buildCumulativeWorkingDayRow($merged, $twoMonthsAgo, $today),
            ],
            'segment_izlence' => $this->buildSegmentStats($merged, $currentMonth, $previousMonth),
            'muvekkil_bazli_aylik_tahsilat' => $this->buildMonthlyClientTotals($merged, $currentMonth),
            // YENİ EKLENEN GRAFİK VERİSİ
            'grafik_verisi' => $this->buildChartData($approved, $currentMonth, $previousMonth, $twoMonthsAgo),
        ];
    }

    public function expectationProtocols(int|string $muvekkilId): array
    {
        $currentMonth = now()->startOfMonth();
        $rows = $this->buildExpectationRows($currentMonth)
            ->firstWhere('muvekkil_id', (string) $muvekkilId);

        if (! $rows) {
            return [
                'muvekkil_ad' => null,
                'hesap_tarihi' => now()->toDateString(),
                'vade_ay' => $currentMonth->format('Y-m'),
                'toplam_kalan_tutar' => 0,
                'protokol_sayisi' => 0,
                'protokoller' => [],
            ];
        }

        return [
            'muvekkil_ad' => $rows['muvekkil_ad'],
            'hesap_tarihi' => now()->toDateString(),
            'vade_ay' => $currentMonth->format('Y-m'),
            'toplam_kalan_tutar' => $rows['toplam_beklenti_tutari'],
            'protokol_sayisi' => count($rows['protokoller']),
            'protokoller' => $rows['protokoller'],
        ];
    }

    public function approvedTahsilatDataset(): Collection
    {
        return Tahsilat::query()
            ->with('muvekkil')
            ->where('onay_durumu', TahsilatOnayDurumu::Onaylandi->value)
            ->get()
            ->map(fn (Tahsilat $tahsilat) => [
                'muvekkil_id' => (string) $tahsilat->muvekkil_id,
                'muvekkil_ad' => $tahsilat->muvekkil?->ad ?? '',
                'normalized_muvekkil_ad' => NameNormalizer::normalize($tahsilat->muvekkil?->ad ?? ''),
                'tahsilat_tarihi' => Carbon::parse($tahsilat->tahsilat_tarihi),
                'tutar' => Money::normalize($tahsilat->tutar),
                'kaynak' => 'gercek',
            ]);
    }

    public function mergedTahsilatDataset(): Collection
    {
        $approved = $this->approvedTahsilatDataset();
        $history = IzlenceGecmisTahsilati::query()
            ->with('muvekkil')
            ->get()
            ->map(fn (IzlenceGecmisTahsilati $kayit) => [
                'muvekkil_id' => (string) $kayit->muvekkil_id,
                'muvekkil_ad' => $kayit->muvekkil?->ad ?? $kayit->ham_muvekkil_adi,
                'normalized_muvekkil_ad' => NameNormalizer::normalize($kayit->muvekkil?->ad ?? $kayit->ham_muvekkil_adi),
                'tahsilat_tarihi' => Carbon::parse($kayit->tahsilat_tarihi),
                'tutar' => Money::normalize($kayit->tutar),
                'kaynak' => 'izlence_gecmis',
            ]);

        return $approved->concat($history)->values();
    }

    private function buildExpectationSummary(CarbonInterface $month): array
    {
        $rows = $this->buildExpectationRows($month);

        return [
            'vade_ay' => $month->format('Y-m'),
            'satirlar' => $rows->map(fn (array $row) => [
                'muvekkil_id' => $row['muvekkil_id'],
                'muvekkil_ad' => $row['muvekkil_ad'],
                'bu_ay_vadesi_gelen_tutari' => $row['bu_ay_vadesi_gelen_tutari'],
                'bu_ay_vadesi_gelecek_tutari' => $row['bu_ay_vadesi_gelecek_tutari'],
                'son_7_gun_vadesi_gecmis_tutari' => $row['son_7_gun_vadesi_gecmis_tutari'],
                'toplam_beklenti_tutari' => $row['toplam_beklenti_tutari'],
            ])->values()->all(),
        ];
    }

    private function buildExpectationRows(CarbonInterface $month): Collection
    {
        $today = now()->startOfDay();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $sevenDaysAgo = $today->copy()->subDays(7);

        return Protokol::query()
            ->with(['muvekkil', 'taksitler'])
            ->where('aktif', true)
            ->get()
            ->groupBy('muvekkil_id')
            ->map(function (Collection $protokoller) use ($today, $start, $end, $sevenDaysAgo) {
                $muvekkil = $protokoller->first()->muvekkil;
                $gelen = '0.00';
                $gelecek = '0.00';
                $sonYedi = '0.00';
                $toplam = '0.00';
                $detailRows = [];

                foreach ($protokoller as $protokol) {
                    $kalanTaksitler = collect($this->protokolService->taksitlerForResponse($protokol))
                        ->filter(fn (array $taksit) => Money::cmp($taksit['kalan_tutar'], '0') === 1);

                    if ($kalanTaksitler->isEmpty()) {
                        continue;
                    }

                    $detailTaksitler = [];
                    $protokolToplam = '0.00';
                    $enYakinVade = null;

                    foreach ($kalanTaksitler as $taksit) {
                        $tarih = Carbon::parse($taksit['taksit_tarihi']);
                        $kalan = Money::normalize($taksit['kalan_tutar']);

                        if ($tarih->between($start, $end)) {
                            $toplam = Money::add($toplam, $kalan);

                            if ($tarih->lte($today)) {
                                $gelen = Money::add($gelen, $kalan);
                            } else {
                                $gelecek = Money::add($gelecek, $kalan);
                            }

                            $detailTaksitler[] = $taksit;
                            $protokolToplam = Money::add($protokolToplam, $kalan);
                            $enYakinVade = $enYakinVade ? min($enYakinVade, $taksit['taksit_tarihi']) : $taksit['taksit_tarihi'];
                        }

                        if ($tarih->lt($today) && $tarih->gte($sevenDaysAgo)) {
                            $sonYedi = Money::add($sonYedi, $kalan);
                        }
                    }

                    if ($detailTaksitler !== []) {
                        $detailRows[] = [
                            'protokol_id' => (string) $protokol->id,
                            'protokol_no' => $protokol->protokol_no,
                            'borclu_adi' => $protokol->borclu_adi,
                            'borclu_tckn_vkn' => $protokol->borclu_tckn_vkn,
                            'muhatap_adi' => $protokol->muhatap_adi,
                            'muhatap_telefon' => $protokol->muhatap_telefon,
                            'en_yakin_vade_tarihi' => $enYakinVade,
                            'protokol_kalan_tutar' => Money::float($protokolToplam),
                            'taksitler' => array_values($detailTaksitler),
                        ];
                    }
                }

                return [
                    'muvekkil_id' => (string) $muvekkil->id,
                    'muvekkil_ad' => $muvekkil->ad,
                    'bu_ay_vadesi_gelen_tutari' => Money::float($gelen),
                    'bu_ay_vadesi_gelecek_tutari' => Money::float($gelecek),
                    'son_7_gun_vadesi_gecmis_tutari' => Money::float($sonYedi),
                    'toplam_beklenti_tutari' => Money::float($toplam),
                    'protokoller' => $detailRows,
                ];
            })
            ->filter(fn (array $row) => $row['toplam_beklenti_tutari'] > 0 || $row['son_7_gun_vadesi_gecmis_tutari'] > 0)
            ->sortByDesc('toplam_beklenti_tutari')
            ->values();
    }

    private function buildWorkingDayRow(Collection $rows, CarbonInterface $month): array
    {
        $monthRows = $rows->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameMonth($month));
        $isGunu = $this->businessDaysInMonth($month);
        $toplam = $this->sumRows($monthRows);

        return [
            'ay' => $month->format('Y-m'),
            'is_gunu' => $isGunu,
            'toplam_tutar' => Money::float($toplam),
            'is_gunu_basi_ortalama' => $isGunu > 0 ? round(Money::float($toplam) / $isGunu, 2) : 0,
        ];
    }

    private function buildCumulativeWorkingDayRow(Collection $rows, CarbonInterface $month, CarbonInterface $today): array
    {
        $ordinal = $this->businessDayOrdinalInCurrentMonth($today);
        $targetDate = $this->nthBusinessDayOfMonth($month, $ordinal) ?? $month->copy()->endOfMonth();

        $periodRows = $rows->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameMonth($month) && $row['tahsilat_tarihi']->lte($targetDate));

        return [
            'ay' => $month->format('Y-m'),
            'kumulatif_tutar' => Money::float($this->sumRows($periodRows)),
            'hedef_tarih' => $targetDate->toDateString(),
        ];
    }

    private function buildSegmentStats(Collection $rows, CarbonInterface $currentMonth, CarbonInterface $previousMonth): array
    {
        $stats = [];

        foreach (config('tahsilat_segments', []) as $segment) {
            $normalizedNames = collect($segment['normalized_muvekkiller'] ?? []);

            $segmentRows = $rows->filter(fn (array $row) => $normalizedNames->contains($row['normalized_muvekkil_ad']));

            $stats[$segment['id']] = [
                'bu_ay_tutar' => Money::float($this->sumRows(
                    $segmentRows->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameMonth($currentMonth))
                )),
                'gecen_ay_tutar' => Money::float($this->sumRows(
                    $segmentRows->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameMonth($previousMonth))
                )),
            ];
        }

        return $stats;
    }

    private function buildMonthlyClientTotals(Collection $rows, CarbonInterface $month): array
    {
        $satirlar = $rows->filter(fn (array $row) => $row['tahsilat_tarihi']->isSameMonth($month))
            ->groupBy('muvekkil_id')
            ->map(function (Collection $grouped) {
                return [
                    'muvekkil_id' => $grouped->first()['muvekkil_id'],
                    'muvekkil_ad' => $grouped->first()['muvekkil_ad'],
                    'toplam_tutar' => Money::float($this->sumRows($grouped)),
                ];
            })
            ->sortByDesc('toplam_tutar')
            ->values()
            ->all();

        return ['satirlar' => $satirlar];
    }

    private function sumRows(Collection $rows): string
    {
        return $rows->reduce(fn (string $toplam, array $row) => Money::add($toplam, $row['tutar']), '0.00');
    }

    private function businessDaysInMonth(CarbonInterface $month): int
    {
        $cursor = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $count = 0;

        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $count++;
            }

            $cursor->addDay();
        }

        return $count;
    }

    private function businessDayOrdinalInCurrentMonth(CarbonInterface $today): int
    {
        $cursor = $today->copy()->startOfMonth();
        $count = 0;

        while ($cursor->lte($today)) {
            if ($cursor->isWeekday()) {
                $count++;
            }

            $cursor->addDay();
        }

        return max($count, 1);
    }

    private function nthBusinessDayOfMonth(CarbonInterface $month, int $ordinal): ?Carbon
    {
        $cursor = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $count = 0;

        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $count++;

                if ($count === $ordinal) {
                    return $cursor->copy();
                }
            }

            $cursor->addDay();
        }

        return null;
    }

    private function buildChartData(Collection $approved, CarbonInterface $currentMonth, CarbonInterface $previousMonth, CarbonInterface $twoMonthsAgo): array
    {
        $currentData = array_fill(1, 31, 0);
        $previousData = array_fill(1, 31, 0);
        $twoMonthsAgoData = array_fill(1, 31, 0);

        $sumCurrent = '0.00';
        $sumPrev = '0.00';
        $sumTwoPrev = '0.00';

        for ($day = 1; $day <= 31; $day++) {
            // Bu Ay
            if ($day <= $currentMonth->daysInMonth) {
                $dailyCurrent = $approved->filter(fn ($row) => $row['tahsilat_tarihi']->isSameMonth($currentMonth) && $row['tahsilat_tarihi']->day === $day);
                $sumCurrent = Money::add($sumCurrent, $this->sumRows($dailyCurrent));
                // Eğer gün bugünden büyükse 0 basma, boş bırak (çizgi bugün kesilsin)
                $currentData[$day] = ($currentMonth->isCurrentMonth() && $day > now()->day) ? null : (float) Money::float($sumCurrent);
            } else {
                $currentData[$day] = null;
            }

            // Geçen Ay
            if ($day <= $previousMonth->daysInMonth) {
                $dailyPrev = $approved->filter(fn ($row) => $row['tahsilat_tarihi']->isSameMonth($previousMonth) && $row['tahsilat_tarihi']->day === $day);
                $sumPrev = Money::add($sumPrev, $this->sumRows($dailyPrev));
                $previousData[$day] = (float) Money::float($sumPrev);
            } else {
                $previousData[$day] = (float) Money::float($sumPrev);
            }

            // 2 Ay Önce
            if ($day <= $twoMonthsAgo->daysInMonth) {
                $dailyTwoPrev = $approved->filter(fn ($row) => $row['tahsilat_tarihi']->isSameMonth($twoMonthsAgo) && $row['tahsilat_tarihi']->day === $day);
                $sumTwoPrev = Money::add($sumTwoPrev, $this->sumRows($dailyTwoPrev));
                $twoMonthsAgoData[$day] = (float) Money::float($sumTwoPrev);
            } else {
                $twoMonthsAgoData[$day] = (float) Money::float($sumTwoPrev);
            }
        }

        return [
            'labels' => range(1, 31),
            'bu_ay' => array_values($currentData),
            'gecen_ay' => array_values($previousData),
            'iki_ay_once' => array_values($twoMonthsAgoData),
        ];
    }
}