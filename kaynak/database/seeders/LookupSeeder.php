<?php

namespace Database\Seeders;

use App\Models\Hacizci;
use App\Models\Muvekkil;
use App\Models\Portfoy;
use App\Support\NameNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        $muvekkilMap = [];
        $hacizciler = $this->hacizciler();

        foreach ($this->muvekkiller() as $kayit) {
            $muvekkil = Muvekkil::query()->updateOrCreate(
                ['normalized_ad' => NameNormalizer::normalize($kayit['ad'])],
                [
                    'ad' => $kayit['ad'],
                    'kod' => $kayit['kod'] ?? null,
                    'aktif' => true,
                ],
            );

            $muvekkilMap[$kayit['ad']] = $muvekkil;
        }

        foreach ($this->portfoyler() as $kayit) {
            $muvekkil = $muvekkilMap[$kayit['muvekkil_ad']] ?? null;

            if (! $muvekkil) {
                continue;
            }

            Portfoy::query()->updateOrCreate(
                [
                    'muvekkil_id' => $muvekkil->id,
                    'normalized_ad' => NameNormalizer::normalize($kayit['ad']),
                ],
                [
                    'ad' => $kayit['ad'],
                    'kod' => $kayit['kod'] ?? null,
                    'aktif' => true,
                ],
            );
        }

        foreach ($this->hacizciler() as $adSoyad) {
            Hacizci::query()->updateOrCreate(
                ['ad_soyad' => $adSoyad],
                [
                    'sicil_no' => null,
                    'kademe' => 'kademe_1',
                    'aktif' => true,
                ],
            );
        }

        $this->removeUnknownHacizciler(collect($hacizciler));
    }

    private function muvekkiller(): array
    {
        return [
            ['ad' => 'GSD Varlık'],
            ['ad' => 'Denge Varlık'],
            ['ad' => 'Birikim Varlık'],
            ['ad' => 'Emir Varlık'],
            ['ad' => 'Doğru Varlık'],
            ['ad' => 'Birleşim Varlık'],
            ['ad' => 'Sümer Varlık'],
            ['ad' => 'Gelecek Varlık'],
            ['ad' => 'Ak Faktoring'],
            ['ad' => 'GSD Faktoring'],
            ['ad' => 'Sümer Faktoring'],
            ['ad' => 'Ulusal Faktoring'],
            ['ad' => 'Eko Faktoring'],
            ['ad' => 'Optima Faktoring'],
            ['ad' => 'Akbank'],
        ];
    }

    private function portfoyler(): array
    {
        $satirlar = [];

        $tumVarliklarPortfoyleri = [
            'Bireysel - Tüm Varlıklar',
            'Kobi - Tüm Varlıklar',
            'Ticari - Tüm Varlıklar',
            'K.T. - Tüm Varlıklar',
        ];

        $varlikMuvekkilleri = [
            'GSD Varlık',
            'Denge Varlık',
            'Birikim Varlık',
            'Emir Varlık',
            'Doğru Varlık',
            'Birleşim Varlık',
            'Sümer Varlık',
            'Gelecek Varlık',
        ];

        foreach ($varlikMuvekkilleri as $muvekkilAd) {
            foreach ($tumVarliklarPortfoyleri as $portfoyAd) {
                $satirlar[] = [
                    'muvekkil_ad' => $muvekkilAd,
                    'ad' => $portfoyAd,
                ];
            }
        }

        foreach ([
            'FİBA 01 - GSD',
            'FİBA 02 - GSD',
            'FİBA 03 - GSD',
            'FİBA 04 - GSD',
            'FİBA 05 - GSD',
            'ANADOLU - GSD',
            'GARANTİBANK - GSD',
            'GARANTİ FAKTORİNG - GSD',
            'İNG - GSD',
            'YKB 01 - GSD',
            'YKB 02 - GSD',
            'YKB 03 - GSD',
            'YKB 04 - GSD',
            'İŞBANK 01 - GSD',
            'İŞBANK 02 - GSD',
            'İŞBANK 03 - GSD',
            'İŞBANK BRY 01 - GSD',
            'İŞBANK BRY 02 - GSD',
            'DENİZBANK - GSD',
            'ŞEKERBANK - GSD',
        ] as $portfoyAd) {
            $satirlar[] = [
                'muvekkil_ad' => 'GSD Varlık',
                'ad' => $portfoyAd,
            ];
        }

        return $satirlar;
    }

    private function hacizciler(): array
    {
        return [
            'ÖMER TÜRÇİN',
            'CİHAD KOL',
            'ERKAN SOYSAL',
            'FIRAT TURAN',
            'UĞUR YILDIRIM',
            'TAHA YASİN ÇULHA',
            'ENES ÇINAROĞLU',
            'MERT KOCABEY',
            'EKİN ULUÇ',
            'EMRE ÖZATEŞ',
            'FATİH İNCEBACAK',
            'ENES DEMİRCİOĞLU',
            'FURKAN PEHLİVAN',
            'EŞREF YILDIRIM',
            'YUSUF TANIŞIR',
            'HASAN SERT',
            'SELÇUK HASAN KARAKUŞ',
            'YUNUS AYDIN OLCAY',
            'OSMAN TÜRÇİN',
            'EMRE CAN ÖZEN',
            'DORUK HIŞIR',
            'ÖMER AZAT KOÇ',
            'HAMZA KAYAN',
            'FATİH KIZILOĞLU',
            'MERT ÖZTÜRK',
            'FATİH BOYRAZ',
            'NURULLAH YILDIRIM',
            'MEHMET ÇAĞRI ERSEKMEN',
            'AHMET KARAGÖZ',
        ];
    }

    private function removeUnknownHacizciler(Collection $allowedNames): void
    {
        $extraHacizciler = Hacizci::query()
            ->whereNotIn('ad_soyad', $allowedNames->all())
            ->get();

        foreach ($extraHacizciler as $hacizci) {
            if ($hacizci->protokoller()->exists()) {
                $hacizci->update(['aktif' => false]);
                continue;
            }

            $hacizci->delete();
        }
    }
}
